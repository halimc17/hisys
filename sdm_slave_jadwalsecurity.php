<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","NOT (tipe='PT' or tipe='HOLDING') and kodeorganisasi='".$idOrg."'",'2',true);
$optPos = makeOption($dbname,"sdm_5possecurity", "nopos,namapos","unit='".$idOrg."'",'',true);
$optShift = makeOption($dbname,"sdm_5shiftsecurity","kodeshift,namashift",'','',true);

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

    $qcount ="select a.kodeorg, b.namaorganisasi, a.periode, a.minggu from ".$dbname.".sdm_jadwalsecurityht a ";
    $qcount .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    $qcount .="WHERE a.kodeorg='".$idOrg."'";
    $qcount .="order by `periode` desc";
    $rcount = fetchData($qcount);
    $jlhbrs = count($rcount);


    $queryAll ="select a.kodeorg, b.namaorganisasi, a.periode, a.pos, c.namapos , a.minggu from ".$dbname.".sdm_jadwalsecurityht a ";
    $queryAll .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    $queryAll .="LEFT JOIN ".$dbname.".sdm_5possecurity c on a.pos=c.nopos ";
    $queryAll .="WHERE a.kodeorg='".$idOrg."'";
    $queryAll .="order by `periode` desc limit " . $offset . "," . $limit . "";
    $resAll = fetchData($queryAll);

    $header = array("Kode Organisasi","Nama Organisasi","Periode","Pos","Minggu Ke-");
    $table ="<fieldset id='fieldForm' style='min-width:500px; clear:right;min-height:auto;'>";
    $table .="<legend>".$_SESSION['lang']['list']."</legend>";
    $table .= "<table cellspacing='1' border='0' class='sortable'>";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td>".$head."</td>";
    }
    $table .= "<td style='width:30px;' colspan=3>*</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody>";
    foreach ($resAll as $key => $row) {
        $queryakun=selectQuery($dbname,"sdm_5periodegaji","sudahproses","periode='".$row['periode']."' and kodeorg='".$row['kodeorg']."' and jenisgaji='B'");
        $resakun=fetchData($queryakun);
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        foreach($row as $col=>$dat) 
        {
            if($col=='pos')
            {
                $table .= "<td hidden id='".$col."_".$key."'>".$dat."</td>";
            }
            else
            {
                $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
            }
        }
            if($resakun[0]['sudahproses']==0)
            {
                $table .= "<td id='edit_".$key."'>";
                $table .= "<img src='images/application/application_edit.png' ";
                $table .= "class=resicon  caption='Edit' onclick='edit(".$key.")'></td>";
                $table .= "<td id='pdf_".$key."'>";
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  caption='Print' onclick=\"masterPDF('sdm_jadwalsecurityht','" . $row['kodeorg'] . "," . $row['periode'] . "," . $row['pos'] . "," . $row['minggu'] . "','','sdm_slave_jadwalsecurityPdf',event)\"></td>";
                $table .= "<td id='delete_".$key."'>";
                $table .= "<img src='images/application/application_delete.png' ";
                $table .= "class=resicon  caption='Delete' onclick='deletehd(".$key.")'></td>";
            }
            else
            {
                $table .= "<td></td>";
                $table .= "<td id='pdf_".$key."'>";
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  caption='Print' onclick=\"masterPDF('sdm_jadwalsecurityht','" . $row['kodeorg'] . "," . $row['periode'] . "," . $row['pos'] . "," . $row['minggu']. "','','sdm_slave_jadwalsecurityPdf',event)\"></td>";
                $table .= "<td></td>";
            }
            $table .= "</tr>";

    }
    $table .="<tr class=rowheader><td colspan=5 align=center>" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
    <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
    <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
    </td>
    </tr>";
    $table .= "</tbody>";
    $table .= "</table>";

    $table .= "</fieldset>";

    echo $table;
    break;
    case 'loadCariData' :
     $limit = 20;
    $page = 0;
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        if ($page < 0)
            $page = 0;
    }
    $offset = $page * $limit;
    $queryAll ="select a.kodeorg, b.namaorganisasi, a.periode, a.pos, c.namapos , a.minggu from ".$dbname.".sdm_jadwalsecurityht a ";
    $queryAll .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    $queryAll .="LEFT JOIN ".$dbname.".sdm_5possecurity c on a.pos=c.nopos ";
    $queryAll .="WHERE a.kodeorg='".$data['kodeorg']."' and a.periode='".$data['periode']."'";
    $queryAll .="order by `periode` desc limit " . $offset . "," . $limit . "";
    $resAll = fetchData($queryAll);
    $jlhbrs = count($resAll);

    $header = array("Kode Organisasi","Nama Organisasi","Periode","Pos","Minggu Ke-");
    $table ="<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
    $table .="<legend>".$_SESSION['lang']['list']."</legend>";
    $table .= "<table cellspacing='1' border='0' class='sortable'>";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td>".$head."</td>";
    }
    $table .= "<td style='width:30px;' colspan=3 align=center>*</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody id='bodyList'>";
    foreach ($resAll as $key => $row) {
        $queryakun=selectQuery($dbname,"sdm_5periodegaji","sudahproses","periode='".$row['periode']."' and kodeorg='".$row['kodeorg']."' and jenisgaji='B'");
        $resakun=fetchData($queryakun);
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        foreach($row as $col=>$dat) 
        {
            if($col=='pos')
            {
                $table .= "<td hidden id='".$col."_".$key."'>".$dat."</td>";
            }
            else
            {
                $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
            }
        }
            if($resakun[0]['sudahproses']==0)
            {
                $table .= "<td id='edit_".$key."'>";
                $table .= "<img src='images/application/application_edit.png' ";
                $table .= "class=resicon  caption='Edit' onclick='edit(".$key.")'></td>";
                $table .= "<td id='pdf_".$key."'>";
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  caption='Print' onclick=\"masterPDF('sdm_jadwalsecurityht','" . $row['kodeorg'] . "," . $row['periode'] . "," . $row['pos'] . "," . $row['minggu'] . "','','sdm_slave_jadwalsecurityPdf',event)\"></td>";
                $table .= "<td id='delete_".$key."'>";
                $table .= "<img src='images/application/application_delete.png' ";
                $table .= "class=resicon  caption='Delete' onclick='deletehd(".$key.")'></td>";
            }
            else
            {
                $table .= "<td></td>";
                $table .= "<td id='pdf_".$key."'>";
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  caption='Print' onclick=\"masterPDF('sdm_jadwalsecurityht','" . $row['kodeorg'] . "," . $row['periode'] . "," . $row['pos'] . "," . $row['minggu']. "','','sdm_slave_jadwalsecurityPdf',event)\"></td>";
                $table .= "<td></td>";
            }
        $table .= "</tr>";
    }
    $table .="<tr class=rowheader><td colspan=5 align=center>" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
    <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
    <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
    </td>
    </tr>";
    $table .= "</tbody>";
    $table .= "</table>";

    $table .= "</fieldset>";

    echo $table;
    break;
    case 'checkHeader':
    $query=selectQuery($dbname,"sdm_jadwalsecurityht","kodeorg","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and minggu='".$data['minggu']."' and pos='".$data['pos']."'");
    $res=fetchData($query);
    $jlmh=count($res);
    
    $where = "";
        // exit('warning : '.$kdOrg);
        $where = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."')  and statuskaryawan != 'Keluar' and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
        $where.=" and kodejabatan in ('95','131','16','45','58','135') ";
    $kquery = selectQuery($dbname,"datakaryawan","karyawanid,namakaryawan",$where);
    $krest = fetchData($kquery);
    $rowkrest = count($krest);
    if($rowkrest==0)
    {
        exit("Warning: Karyawan pada ".$optOrg[$data['kodeorg']]." dengan jabatan security tidak ada");
    }
    else if($jlmh!=0)
    {
        exit("Warning: Data ".$optOrg[$data['kodeorg']]." , periode ".$data['periode'].", minggu ke-".$data['minggu'].", Pos ".$optPos[$data['pos']]." sudah ada");
    }
    else
    {
        $datahd = array('kodeorg'=>$data['kodeorg'], 'periode'=>$data['periode'],'minggu'=>$data['minggu'],'pos'=>$data['pos']);
        $qIns = insertQuery($dbname,'sdm_jadwalsecurityht',$datahd);
        try{
            $owlPDO->exec($qIns); 
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
        }

    }

    break;
    case 'getWeek':
     $query=selectQuery($dbname,"sdm_5periodegaji","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and jenisgaji='B' ");
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
        $query=selectQuery($dbname,"sdm_5periodegaji","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and jenisgaji='B' ");
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
        $where = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."')  and statuskaryawan != 'Keluar'  and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
        $where.=" and kodejabatan in ('95','131','16','45','58','135') ";
        $optKary = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan",$where,'',true);

        $formdet .= "<tr id='tr_".$data['nomer']."' class='rowcontent' style='cursor:pointer'>";
        $formdet .= "<td>".makeElement('karyawanid_'.$data['nomer'],'selectsearch','','',$optKary)."</td>";
        foreach ($listTgl[$data['minggu']] as $key => $value)
        {   
            if($btsbwTgl==0)
            {
                $btsbwTgl=intval($value);
            }
            $formdet .= "<td align=center>".makeElement('shift_'.$data['nomer'].'_'.intval($value),'select','',array('style'=>'width:75px'),$optShift)."</td>";
            $btsatgl=intval($value);
        }
        $formdet .="</tr>";

        echo $formdet;


    break;
    case 'getDetail':
    $query=selectQuery($dbname,"sdm_5periodegaji","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and jenisgaji='B' ");
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
    $where = "";
        // exit('warning : '.$kdOrg);
        $where = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."')  and statuskaryawan != 'Keluar'  and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
        $where.=" and kodejabatan in ('95','131','16','45','58','135') ";
    $optKary = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan",$where,'',true);
    
    //exit('kres = '.$krest[0]['namakaryawan']);
    foreach ($listTgl[$data['minggu']] as $key => $value)
        {   
            if($btsbwTgl==0)
            {
                $btsbwTgl=intval($value);
            }
            $btsatgl=intval($value);
        }
    $formdet ="<fieldset id='fieldFormDet' clear:right;min-height:auto;'>";
    $formdet .="<legend>Rincian</legend>";
    $formdet .= "<table cellspacing='1' border='0' class='sortable'>";
    $formdet .= "<thead><tr class='rowheader'>";
    $formdet .= "<td rowspan=2>Nama Karyawan</td>";
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {
        $formdet .= "<td style='width:75px;' align=center >".$value."</td>";
    }
    $formdet .= "<td rowspan=2><img src=images/plus.gif ";
        $formdet .= "class=resicon  title='Add Detail ' onclick='tambahInputDetail(".$btsbwTgl.",".$btsatgl.")'></td>";
        $formdet .= "<td rowspan=2><img src=images/minus.gif ";
        $formdet .= "class=resicon  title='Delete Detail ' onclick='kurangInputDetail()'></td></tr>";
    $formdet .= "<tr>";
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {
        $formdet .= "<td>Shift</td>";
    }
    $formdet .= "</tr>";
    $formdet .="</thead>";
    $formdet .= "<tbody id='formListdetailx'>";
    $btsbwTgl=0;
    $btsatgl=0;
        $formdet .= "<tr id='tr_1' class='rowcontent' style='cursor:pointer'>";
        $formdet .= "<td>".makeElement('karyawanid_1','selectsearch','','',$optKary)."</td>";
        foreach ($listTgl[$data['minggu']] as $key => $value)
        {   
            if($btsbwTgl==0)
            {
                $btsbwTgl=intval($value);
            }
            $formdet .= "<td align=center>".makeElement('shift_1_'.intval($value),'select','',array('style'=>'width:75px'),$optShift)."</td>";
            $btsatgl=intval($value);
        }
        $formdet .="</tr>";
    
    $formdet .= "</tbody>";
    $formdet .= "</table>";
    $formdet .=  makeElement('saveDetailButton','button',$_SESSION['lang']['save'],array('onclick'=>'saveData('.$btsbwTgl.','.$btsatgl.')'));

    $formdet .= "</fieldset>";
    echo $formdet;
    
    break;
    case 'getEditDetail':
    $query=selectQuery($dbname,"sdm_5periodegaji","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and jenisgaji='B' ");
    $res=fetchData($query);
    $minggu=0;
    $listTgl=array();
    $batasAtas=intval(substr($res[0]['tanggalsampai'],-2,2));
    for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
        if($tglawal<10){
            $strTgl=$res[0]['periode']."-0".$tglawal;
        }
        else{
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
    $kwhere = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."') and statuskaryawan != 'Keluar'  and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
    $kwhere.=" and kodejabatan in ('95','131','16','45','58','135') ";
    $optKaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan",$kwhere);
    $formdet ="<fieldset id='fieldFormDet' clear:right;min-height:auto;'>";
    $formdet .="<legend>Rincian</legend>";
    $formdet .= "<table cellspacing='1' border='0' class='sortable'>";
    $formdet .= "<thead><tr class='rowheader'>";
    $formdet .= "<td rowspan=2>Nama Karyawan</td>";
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {
        $formdet .= "<td style='width:75px;' align=center >".$value."</td>";
    }
    $formdet .= "<td rowspan=2>Action</td>";
    $formdet .= "</tr>";
    $formdet .= "<tr>";
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {
        $formdet .= "<td>Shift</td>";
    }
    $formdet .= "</tr>";
    $formdet .= "</thead>";
    $formdet .= "<tbody id='formListdetail'>";
    $formdet .= "<tr class='rowcontent' style='cursor:pointer'>";
    $formdet .= "<td>".makeElement('notransaksi','hidden','')."";
    $formdet .= "".makeElement('karyawanid','select','',array('style'=>'width:100px'),$optKaryawan)."</td>";
    $no=1;
    $btsbwTgl=0;
    $btsatgl=0;
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {   
        if($btsbwTgl==0)
        {
            $btsbwTgl=intval($value);
        }
        $formdet .= "<td align=center>".makeElement('shift_'.intval($value),'select','',array('style'=>'width:75px'),$optShift)."</td>";
        $btsatgl=intval($value);
    }
    $formdet .= "<td>".makeElement('updateDetailButton','button',$_SESSION['lang']['save'],array('onclick'=>'saveDataDetail('.$btsbwTgl.','.$btsatgl.')'))."</td>";
    $formdet .="</tr>";
    $formdet .= "</tbody>";
    $formdet .= "</table>";
    $formdet .= "</fieldset>";
    echo $formdet;
    break;
    case 'loadDataDetail':
    $query=selectQuery($dbname,"sdm_5periodegaji","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and jenisgaji='B' ");
    $res=fetchData($query);
    $minggu=0;
    $listTgl=array();
    $batasAtas=intval(substr($res[0]['tanggalsampai'],-2,2));
    for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
        if($tglawal<10){
            $strTgl=$res[0]['periode']."-0".$tglawal;
        }
        else{
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
    $id=$data['kodeorg']."/".$data["periode"]."/".$data["pos"]."/".$data["minggu"];
    $queryht=selectQuery($dbname,"sdm_jadwalsecuritydt","karyawanid","notransaksi='".$id."'","karyawanid asc",true);
    $resht=fetchData($queryht);
    $kwhere = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."')  and statuskaryawan != 'Keluar'  and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
    $kwhere.=" and kodejabatan in ('95','131','16','45','58','135') ";
    $optKaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan",$kwhere);
    //print_r($resht);
    //exit();
    $tabdet ="<fieldset id='fieldFormDet' clear:right;min-height:auto;'>";
    $tabdet .="<legend>List Data Detail</legend>";
    $tabdet .= "<table cellspacing='1' border='0' class='sortable'>";
    $tabdet .= "<thead><tr class='rowheader'>";
    $tabdet .= "<td rowspan=2>Nama Karyawan</td>";
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {
        $tabdet .= "<td style='width:75px;' align=center >".$value."</td>";
    }
    $tabdet .= "<td rowspan=2 colspan=2>Action</td>";
    $tabdet .= "</tr>";
    $tabdet .= "<tr>";
    foreach ($listTgl[$data['minggu']] as $key => $value)
    {
        $tabdet .= "<td>Shift</td>";
    }
    $tabdet .= "</tr>";
    $tabdet .= "</thead>";
    $tabdet .= "<tbody id='formListdetail'>";
    $btsbwTgl=0;
    $btsatgl=0;
    foreach ($resht as $keyht => $valht) {
        $tabdet .= "<tr class='rowcontent' style='cursor:pointer'>";
        $tabdet .= "<td hidden id='id_".$keyht."'>".$id."</td>";
        $tabdet .= "<td hidden id='karyawanid_".$keyht."'>".$valht['karyawanid']."</td>";
        $tabdet .= "<td id='karyawannama_".$keyht."'>".$optKaryawan[$valht['karyawanid']]."</td>";
        foreach ($listTgl[$data['minggu']] as $key => $value)
        {   
            if($btsbwTgl==0)
            {
                $btsbwTgl=intval($value);
            }
            $querydt=selectQuery($dbname,"sdm_jadwalsecuritydt","kodeshift","notransaksi='".$id."' and date_format(tanggal,'%d-%m-%Y')='".$value."' and karyawanid='".$valht['karyawanid']."'","karyawanid asc");
            $resdt=fetchData($querydt);
            $tabdet .= "<td hidden id='shift_".$keyht."_".intval($value)."'>".$resdt[0]['kodeshift']."</td>";
            $tabdet .= "<td align=center id='shiftnama_".$keyht."_".intval($value)."'>".$optShift[$resdt[0]['kodeshift']]."</td>";
            $btsatgl=intval($value);
        }  
    
    $tabdet .= "<td id='editDetail_".$keyht."'>";
    $tabdet .= "<img src='images/application/application_edit.png' ";
    $tabdet .= "class=resicon  caption='Edit' onclick='editDetail(".$keyht.",".$btsbwTgl.",".$btsatgl.")'></td>";
    $tabdet .= "<td id='deleteDetail_".$keyht."'>";
    $tabdet .= "<img src='images/application/application_delete.png' ";
    $tabdet .= "class=resicon  caption='Delete' onclick='deleteDetail(".$keyht.",".$btsbwTgl.",".$btsatgl.")'></td>";

    $tabdet .= "</tr>";
    }
    $tabdet .= "</tbody>";
    $tabdet .= "</table>";
    $tabdet .= "</fieldset>";
    echo $tabdet;
    break;
    case 'saveData':
    $karyawanid = explode('###', $data['karyawanid']);
    $shift = explode('###', $data['shift']);
    
    $id=$data['kodeorg']."/".$data["periode"]."/".$data["pos"]."/".$data["minggu"];
    $datadt=array();
    $no=0;
    foreach ($karyawanid as $key => $val) {
        
        $datadt['notransaksi']=$id;
        $datadt['karyawanid']=$val;
        for ($t=$data['batasbawah'];$t<=$data['batasatas'];$t++) {
            if($t<10){
                $datadt['tanggal']=$data['periode']."-0".$t;
            }else{
                $datadt['tanggal']=$data['periode']."-".$t;
            }
            $datadt['kodeshift']=$shift[$no];
            $no++;
            //print_r($datadt);
            //exit();
            $qdetIns = insertQuery($dbname,'sdm_jadwalsecuritydt',$datadt);
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
        }
    }
    //echo $datahd;
    break;
    case 'updateData':
    $shift = explode('###', $data['shift']);
    $tanggal='';
    $datadt=array();
    $datadins=array();

    $no=0;
        $datadins['notransaksi']=$data['notransaksi'];
        $datadins['karyawanid']=$data['karyawanid'];
        for ($t=$data['batasbawah'];$t<=$data['batasatas'];$t++) {
            if($t<10){
                $tanggal=$data['periode']."-0".$t;
                $datadins['tanggal']=$tanggal;
            }else{
                $tanggal=$data['periode']."-".$t;
                $datadins['tanggal']=$tanggal;
            }
            $datadt['kodeshift']=$shift[$no];
            $datadins['kodeshift']=$shift[$no];
            $no++;
            $query=selectQuery($dbname,"sdm_jadwalsecuritydt","*","notransaksi='".$data['notransaksi']."' and karyawanid='".$data['karyawanid']."' and tanggal='".$tanggal."'");
            $res=fetchData($query);
            if(count($res)!=0)
            {
                $qdetIns = updateQuery($dbname,"sdm_jadwalsecuritydt",$datadt,"notransaksi='".$data['notransaksi']."' and karyawanid='".$data['karyawanid']."' and tanggal='".$tanggal."'");
            }
            else
            {
                $qdetIns = insertQuery($dbname,"sdm_jadwalsecuritydt",$datadins);
            }
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
   
            $qdetIns = deleteQuery($dbname,"sdm_jadwalsecurityht","kodeorg='".$data['kodeorg']."' and periode='".$data['periode']."' and minggu='".$data['minggu']."' and pos='".$data['pos']."'");
            //print_r($qdetIns);
            //exit();
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }

            $qdetInsdet = deleteQuery($dbname,"sdm_jadwalsecuritydt","notransaksi='".$data['kodeorg']."/".$data['periode']."/".$data['minggu']."'");
            //print_r($qdetIns);
            //exit();
            try{
                $owlPDO->exec($qdetInsdet); 
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
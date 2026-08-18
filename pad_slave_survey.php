<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$proses = checkPostGet('proses', '');
$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe='PT'",'',true);
$optTipe = makeOption($dbname,"pad_5typesurvey","kodesurvey,namasurvey",'','',true);
switch($proses) {
    
        case 'simpanData':

        $query = selectQuery($dbname,"pad_surveyht","notransaksi");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['notransaksi']>=$maxid ? $maxid=$row['notransaksi'] : false;
        }
        $maxid++;
        }
        $data['notransaksi']=$maxid;

        $sql=insertQuery($dbname,"pad_surveyht",$data);
        try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

        echo $data['notransaksi'];
        break;

        case 'simpanDataDetail1':

        $query = selectQuery($dbname,"pad_surveydt","id");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['id']>=$maxid ? $maxid=$row['id'] : false;
        }
        $maxid++;
        }
        $data['id']=$maxid;

        $sql=insertQuery($dbname,"pad_surveydt",$data);
        try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

        echo $data['id'];
        break;

        case 'simpanDataDetail2':

       
        $sub1= explode('###', $data['sub1']);
        $sub2= explode('###', $data['sub2']);
        $datains=array();
        if($sub1[0]!='')
        {
            foreach ($sub1 as $key => $val) {
                    $query = selectQuery($dbname,"pad_surveydt2","id");
                    $id = fetchData($query);
                    $maxid=1;
                    if(!empty($id)) {
                    foreach($id as $row) {
                    $row['id']>=$maxid ? $maxid=$row['id'] : false;
                    }
                    $maxid++;
                    }
                    $data['id']=$maxid;
               $sub1det= explode('/', $val);
               $datains['id']=$data['id'];
               $datains['induk']=$data['induk'];
               $datains['subjenis']=$sub1det[0];
               $datains['rincian']='-';
               $datains['keterangan']=$sub1det[1];
               $sql=insertQuery($dbname,"pad_surveydt2",$datains);
               try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            }
        }

        $datains=array();
        if($sub2[0]!='')
        {
            foreach ($sub2 as $key => $val) {
                $query = selectQuery($dbname,"pad_surveydt2","id");
                    $id = fetchData($query);
                    $maxid=1;
                    if(!empty($id)) {
                    foreach($id as $row) {
                    $row['id']>=$maxid ? $maxid=$row['id'] : false;
                    }
                    $maxid++;
                    }
                    $data['id']=$maxid;
               $sub2det= explode('/', $val);
               $datains['id']=$data['id'];
               $datains['induk']=$data['induk'];
               $datains['subjenis']=$sub2det[0];
               $datains['rincian']=$sub2det[1];
               $datains['keterangan']=$sub2det[2];
               $sql=insertQuery($dbname,"pad_surveydt2",$datains);
               try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            }
        }
        print_r($datains);
        break;

        case 'loadData':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $qcount ="select a.notransaksi, a.kodeorg, b.namaorganisasi from ".$dbname.".pad_surveyht a ";
        $qcount .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
        $qcount .="order by `notransaksi` desc";
        $rcount = fetchData($qcount);
        $jlhbrs = count($rcount);


        $queryAll ="select a.notransaksi, a.kodeorg, b.namaorganisasi from ".$dbname.".pad_surveyht a ";
        $queryAll .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
        $queryAll .="order by `notransaksi` desc limit " . $offset . "," . $limit . "";
        $resAll = fetchData($queryAll);

        $header = array("No Transaksi","Kode Perusahaan","Nama Perusahaan");
        $table ="<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
        $table .="<legend>".$_SESSION['lang']['list']."</legend>";
        $table .= "<table cellspacing='1' border='0' class='sortable'>";
        $table .= "<thead><tr class='rowheader'>";
        foreach($header as $head) {
            $table .= "<td>".$head."</td>";
        }
        $table .= "<td style='width:30px;' colspan=4>*</td>";
        $table .= "</tr></thead>";
        $table .= "<tbody>";
        foreach ($resAll as $key => $row) {
           /* $queryakun=selectQuery($dbname,"setup_periodeakuntansi","tutupbuku","periode='".$row['periode']."' and kodeorg='".$row['kodeorg']."'");
            $resakun=fetchData($queryakun);*/
            $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
            foreach($row as $col=>$dat) 
            {
                $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
            }
               /* if($resakun[0]['tutupbuku']==0)
                {*/
                    $table .= "<td id='edit_".$key."'>";
                    $table .= "<img src='images/application/application_edit.png' ";
                    $table .= "class=resicon  caption='Edit' onclick='edit(".$key.")'></td>";
                    $table .= "<td><a href='#' onclick=dataKeExcel(event,'pad_slave_survey.php','".$row['notransaksi']."')><img  src=images/excel.jpg class=resicon title='MS.Excel'></a></td>";
                    $table .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$row['notransaksi']."')\" src='images/upload-2-xxl.png'/></td><td id='delete_".$key."'>";
                    $table .= "<img src='images/application/application_delete.png' ";
                    $table .= "class=resicon  caption='Delete' onclick='deletehd(".$key.")'></td>";
                /*}
                else
                {
                    $table .= "<td></td>";
                    $table .= "<td id='pdf_".$key."'>";
                    $table .= "<img src='images/pdf.jpg' ";
                    $table .= "class=resicon  caption='Print' onclick=\"masterPDF('sdm_jadwalsecurityht','" . $row['kodeorg'] . "," . $row['periode'] . "," . $row['minggu']. "','','sdm_slave_jadwalsecurityPdf',event)\"></td>";
                    $table .= "<td></td>";
                }*/
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
        
        case 'loadDataCari':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $qcount ="select a.notransaksi, a.kodeorg, b.namaorganisasi from ".$dbname.".pad_surveyht a ";
        $qcount .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
        $qcount .="where kodeorg='".$data['kodeorg']."'";
        $qcount .="order by `notransaksi` desc";
        $rcount = fetchData($qcount);
        $jlhbrs = count($rcount);


        $queryAll ="select a.notransaksi, a.kodeorg, b.namaorganisasi from ".$dbname.".pad_surveyht a ";
        $queryAll .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
        $queryAll .="where kodeorg='".$data['kodeorg']."'";
        $queryAll .="order by `notransaksi` desc limit " . $offset . "," . $limit . "";
        $resAll = fetchData($queryAll);

        $header = array("No Transaksi","Kode Perusahaan","Nama Perusahaan");
        $table ="<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
        $table .="<legend>".$_SESSION['lang']['list']."</legend>";
        $table .= "<table cellspacing='1' border='0' class='sortable'>";
        $table .= "<thead><tr class='rowheader'>";
        foreach($header as $head) {
            $table .= "<td>".$head."</td>";
        }
        $table .= "<td style='width:30px;' colspan=4>*</td>";
        $table .= "</tr></thead>";
        $table .= "<tbody>";
        foreach ($resAll as $key => $row) {
           /* $queryakun=selectQuery($dbname,"setup_periodeakuntansi","tutupbuku","periode='".$row['periode']."' and kodeorg='".$row['kodeorg']."'");
            $resakun=fetchData($queryakun);*/
            $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
            foreach($row as $col=>$dat) 
            {
                $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
            }
               /* if($resakun[0]['tutupbuku']==0)
                {*/
                    $table .= "<td id='edit_".$key."'>";
                    $table .= "<img src='images/application/application_edit.png' ";
                    $table .= "class=resicon  caption='Edit' onclick='edit(".$key.")'></td>";
                    $table .= "<td><a href='#' onclick=dataKeExcel(event,'pad_slave_survey.php','".$row['notransaksi']."')><img  src=images/excel.jpg class=resicon title='MS.Excel'></a></td>";
                    $table .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$row['notransaksi']."')\" src='images/upload-2-xxl.png'/></td><td id='delete_".$key."'>";
                    $table .= "<img src='images/application/application_delete.png' ";
                    $table .= "class=resicon  caption='Delete' onclick='deletehd(".$key.")'></td>";
                /*}
                else
                {
                    $table .= "<td></td>";
                    $table .= "<td id='pdf_".$key."'>";
                    $table .= "<img src='images/pdf.jpg' ";
                    $table .= "class=resicon  caption='Print' onclick=\"masterPDF('sdm_jadwalsecurityht','" . $row['kodeorg'] . "," . $row['periode'] . "," . $row['minggu']. "','','sdm_slave_jadwalsecurityPdf',event)\"></td>";
                    $table .= "<td></td>";
                }*/
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

        case 'deletehd':
         $sqldel = deleteQuery($dbname,"pad_surveyht","notransaksi='".$data['notransaksi']."'");
            try{
                $owlPDO->exec($sqldel); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }

        break;

        case 'adddataform':
        $form= "<fieldset id='formsurvey' clear:right;min-height:auto;'>";
        $form.= "<legend align=center>Form Survey</legend>";

        $form.= "<table border=0 cellspacing=1>";
        $form.= "<tr>";
        $form.= "<td>No Transaksi</td>";
        $form.= "<td>:</td>";
        $form.= "<td>".makeElement('notransaksi','textnum','',array('style'=>'width:160px','disabled'=>'disabled'))."</td>";
        $form.= "</tr>";
        $form.= "<tr>";
        $form.= "<td>Nama Perusahaan</td>";
        $form.= "<td>:</td>";
        $form.= "<td>".makeElement('kodeorg','selectsearch','',array('style'=>'width:160px'),$optOrg)."</td>";
        $form.= "</tr>";
        $form.= "<tr>";
        $form.= "<td>Kategori</td>";
        $form.= "<td>:</td>";
        $form.= "<td>".makeElement('tipe','selectsearch','',array('style'=>'width:160px'),$optTipe)."</td>";
        $form.= "</tr>";
        $form.= "</table>";

        $form.= "<hr>";
        $form.= "<table border=0 cellspacing=2>";
        $form.= "<th>";
        $form.= "<tr><td><span style='font-weight:bold'>Jenis Survey</span></td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type='text' class='optjawaban' id='jenissurvey'/> </td></tr>";
        $form.= "</th>";
        $form.= "</table>";
        $form.= "<table border=0 cellspacing=2>";
        $form.= "<th>";
        $form.= "<tr><td><span style='font-weight:bold'>Sub 1 Jenis Survey&nbsp;:<img src=images/plus.png ";
        $form.= "class=resicon  title='Add Detail ' onclick='tambahRincian()'></span>";
        $form.= "</td>";
        $form.= "<td></td>";
        $form.= "<td></td></tr></th>";
        $form.= "<tbody id='bodyList1'>";
        $form.= "</tbody>";
        $form.= "</table>";
        $form.= "<table id=submenu2 border=0 cellspacing=2>";
        $form.= "<th>";
        $form.= "<tr><td><span style='font-weight:bold'>Sub 2 Jenis Survey&nbsp;:<img src=images/plus.png ";
        $form.= "class=resicon  title='Add Detail ' onclick='tambahRincian1()'></span>";
        $form.= "</td>";
        $form.= "<td></td>";
        $form.= "<td></td></tr></th>";
        $form.= "<tbody id='bodyList2'>";
        $form.= "</tbody>";
        $form.= "</table>";
        $form.= makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>'addData()'));
        $form.= "</fieldset>";

        echo $form;
        break;
        case 'loadDataEdit':
        $sqlht = selectQuery($dbname,"pad_surveyht","notransaksi,kodeorg","notransaksi='".$data['notransaksi']."'","notransaksi asc");
        $resht = fetchdata($sqlht);

        $sqldt = selectQuery($dbname,"pad_surveydt","*","induk='".$data['notransaksi']."'","id asc");
        $resdt = fetchdata($sqldt);

        $sqldtsubi = selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and rincian='-' ","id asc");
        $resdt2sub1 = fetchdata($sqldtsubi);
        $jlhsub1 = count($resdt2sub1);

        $sqldtsubo = selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and rincian !='-' and rincian is not null");
        $resdt2sub2 = fetchdata($sqldtsubo);
        $jlhsub2 = count($resdt2sub2);

        $sqldiscint = selectQuery($dbname,"pad_surveydt2","subjenis","induk='".$resdt[0]['id']."' and rincian !='-' and rincian is not null","id asc",true);
        $ressubjenis2 = fetchdata($sqldiscint);
        $jlhsubjenis2 = count($ressubjenis2);

        $form= "<fieldset id='formsurvey' clear:right;min-height:auto;'>";
        $form.= "<legend align=center>Form Survey</legend>";

        $form.= "<table border=0 cellspacing=1>";
        $form.= "<tr>";
        $form.= "<td>No Transaksi</td>";
        $form.= "<td>:</td>";
        $form.= "<td>".makeElement('notransaksi','textnum',$resht[0]['notransaksi'],array('style'=>'width:160px','disabled'=>'disabled'))."</td>";
        $form.= "</tr>";
        $form.= "<tr>";
        $form.= "<td>Nama Perusahaan</td>";
        $form.= "<td>:</td>";
        $form.= "<td>".makeElement('kodeorg','selectsearch',$resht[0]['kodeorg'],array('style'=>'width:160px','disabled'=>'disabled'),$optOrg)."</td>";
        $form.= "</tr>";
        $form.= "<tr>";
        $form.= "<td>Kategori</td>";
        $form.= "<td>:</td>";
        $form.= "<td>".makeElement('tipe','selectsearch',$resdt[0]['tipe'],array('style'=>'width:160px'),$optTipe)."</td>";
        $form.= "</tr>";
        $form.= "</table>";

        $form.= "<hr>";
        $form.= "<table border=0 cellspacing=2>";
        $form.= "<th>";
        $form.= "<tr><td><span style='font-weight:bold'>Jenis Survey</span></td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type='hidden' id=iddetail1 value='".$resdt[0]['id']."' /><input type='text' class='optjawaban' id='jenissurvey' value='".$resdt[0]['jenis']."'/> </td></tr>";
        $form.= "</th>";
        $form.= "</table>";
        $form.= "<table border=0 cellspacing=2>";
        $form.= "<th>";
        $form.= "<tr><td><span style='font-weight:bold'>Sub 1 Jenis Survey&nbsp;:<img src=images/plus.png ";
        $form.= "class=resicon  title='Add Detail ' onclick='tambahRincian()'></span>";
        $form.= "</td>";
        $form.= "<td></td>";
        $form.= "<td></td></tr></th>";
        $form.= "<tbody id='bodyList1'>";
        $nos=1;
        foreach ($resdt2sub1 as $keysub1 => $valsub1) {
            $form.= "<tr id='rincian1_".$nos."'><td>&nbsp;&nbsp;".$nos.".<input type='hidden' value='".$valsub1['id']."' id='id_".$nos."'/><input type='text' class='optjawaban' id='rincianval_".$nos."' value='".$valsub1['subjenis']."'/></td>";
            if($jlhsub2==0){
                $form.= "<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_".$nos."' value='".$valsub1['keterangan']."'/></td>";
            }
            else{
                $form.= "<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_".$nos."' disabled/></td>";
            }
            if($jlhsub1==$nos)
            {
                $form.="<td id='delcon_".$nos."'><a id='delete_".$nos."'><img src=images/delete.png ";
                $form.="class=resicon  title='Delete ' onclick='deleteRincian(".$nos.")'></a></td></tr>";
            }
            else
            {
                $form.="<td id='delcon_".$nos."'></td></tr>";
            }
            $nos++;
        }
        $form.= "</tbody>";
        $form.= "</table>";
        $form.= "<table id=submenu2 border=0 cellspacing=2>";
        $form.= "<th>";
        $form.= "<tr><td><span style='font-weight:bold'>Sub 2 Jenis Survey&nbsp;:<img src=images/plus.png ";
        $form.= "class=resicon  title='Add Detail ' onclick='tambahRincian1()'></span>";
        $form.= "</td>";
        $form.= "<td></td>";
        $form.= "<td></td></tr></th>";
        $form.= "<tbody id='bodyList2'>";
        $nox=1;
        foreach ($ressubjenis2 as $keysub2 => $valsub2) {
            $form.= "<tr id='headlist2_".$nox."'><td>&nbsp;&nbsp;".$nox.".<input type='text' class='optjawaban' id='subval_".$nox."' value='".$valsub2['subjenis']."'/></td>";
            $form.= "<td>";
            $form.= "<div id='rinciansub_".$nox."'>Rincian&nbsp;:<img src=images/plus.png ";
            $form.= "class=resicon  title='Add Detail ' onclick='tambahSubRincian(".$nox.")'>";
            $sqlsubsub=selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and subjenis='".$valsub2['subjenis']."'","id asc");
            $ressubsub=fetchdata($sqlsubsub);
            $jlhsubsub=count($ressubsub);
            $nop=1;
            foreach ($ressubsub as $key => $val) {
                if($jlhsubsub==$nop){
                    $form.="<table id='rincian_".$nop."_subval_".$nox."'><tr><td>&nbsp;&nbsp;".$nop.".<input type='hidden' value='".$val['id']."' id='id_".$nop."_subval_".$nox."'/><input type='text' class='optjawaban' id='rincianval_".$nop."_subval_".$nox."' value='".$val['rincian']."'/></td>";
                    $form.="<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_".$nop."_subval_".$nox."' value='".$val['keterangan']."'/></td>";
                    $form.="<td id='delcon_".$nop."_subval_".$nox."'><a id='delete_".$nop."_subval_".$nox."'><img src=images/delete.png ";
                    $form.="class=resicon  title='Delete ' onclick='deleteSubRincian(".$nop.",".$nox.")'></a></td></tr></table>";
                    $nop++;
                }
                else
                {
                    $form.="<table id='rincian_".$nop."_subval_".$nox."'><tr><td>&nbsp;&nbsp;".$nop.".<input type='hidden' value='".$val['id']."' id='id_".$nop."_subval_".$nox."'/><input type='text' class='optjawaban' id='rincianval_".$nop."_subval_".$nox."' value='".$val['rincian']."'/></td>";
                    $form.="<td>&nbsp;&nbsp;Ket.<input type='text' class='optjawaban' id='ketval_".$nop."_subval_".$nox."' value='".$val['keterangan']."'/></td>";
                    $form.="<td id='delcon_".$nop."_subval_".$nox."'></td></tr></table>";
                    $nop++;
                }
            }
            $form.= "</div></td><td>";
            $nox++;
        }
        $form.= "</tbody>";
        $form.= "</table>";
        $form.= makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>'updateDataDetail1('.$data['notransaksi'].')'));
        $form.= "</fieldset>";

        echo $form;

        break;
        case 'updateDataDetail1':
        unset($data['id']);
        $sql=updateQuery($dbname,"pad_surveydt",$data,"id='".$_POST['id']."'");
        try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

        echo $_POST['id'];
        break;

        case 'updateDataDetail2':

       
        $sub1= explode('###', $data['sub1']);
        $sub2= explode('###', $data['sub2']);
        $datains=array();
        $dataupd=array();
        if($sub1[0]!='')
        {
            foreach ($sub1 as $key => $val) {
               $sub1det= explode('/', $val);
               
               if($sub1det[0]!='')
               {
                    $ids=$sub1det[0];
                    $dataupd['induk']=$data['induk'];
                    $dataupd['subjenis']=$sub1det[1];
                    $dataupd['rincian']='-';
                    $dataupd['keterangan']=$sub1det[2];
                    $sql=updateQuery($dbname,"pad_surveydt2",$dataupd,"id='".$ids."'");
                    try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
               }
               else
               {
                    $query = selectQuery($dbname,"pad_surveydt2","id");
                    $id = fetchData($query);
                    $maxid=1;
                    if(!empty($id)) {
                    foreach($id as $row) {
                    $row['id']>=$maxid ? $maxid=$row['id'] : false;
                    }
                    $maxid++;
                    }
                    $datains['id']=$maxid;
                    $datains['induk']=$data['induk'];
                    $datains['subjenis']=$sub1det[1];
                    $datains['rincian']='-';
                    $datains['keterangan']=$sub1det[2];

                    $sql=insertQuery($dbname,"pad_surveydt2",$datains);
                    try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                 
               }
              
            }
        }

        $datains=array();
        if($sub2[0]!='')
        {
            foreach ($sub2 as $key => $val) {

               $sub2det= explode('/', $val);
               if($sub2det[0]!='')
               {
                    $ids=$sub2det[0];
                    $dataupd['induk']=$data['induk'];
                    $dataupd['subjenis']=$sub2det[1];
                    $dataupd['rincian']=$sub2det[2];
                    $dataupd['keterangan']=$sub2det[3];
                    $sql=updateQuery($dbname,"pad_surveydt2",$dataupd,"id='".$ids."'");
                    try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
               }
               else
               {
                    $query = selectQuery($dbname,"pad_surveydt2","id");
                    $id = fetchData($query);
                    $maxid=1;
                    if(!empty($id)) {
                    foreach($id as $row) {
                    $row['id']>=$maxid ? $maxid=$row['id'] : false;
                    }
                    $maxid++;
                    }
                    $datains['id']=$maxid;
                    $datains['induk']=$data['induk'];
                    $datains['subjenis']=$sub2det[1];
                    $datains['rincian']=$sub2det[2];
                    $datains['keterangan']=$sub2det[3];
                    $sql=insertQuery($dbname,"pad_surveydt2",$datains);
                    try{$owlPDO->exec($sql); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
               }
            }
        }

        print_r($datains);
        break;
        case 'deletedatasubrincian':
        $sqldel = deleteQuery($dbname,"pad_surveydt2","id='".$data['id']."'");
            try{
                $owlPDO->exec($sqldel); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
        break;
        case 'deletedatarincian':
        $sqldel = deleteQuery($dbname,"pad_surveydt2","id='".$data['id']."'");
            try{
                $owlPDO->exec($sqldel); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
        break;
        case 'excel':
        $notransaksi = checkPostGet('notransaksi', '');

        $sqlht = selectQuery($dbname,"pad_surveyht","notransaksi,kodeorg","notransaksi='".$notransaksi."'","notransaksi asc");
        $resht = fetchdata($sqlht);

        $sqldt = selectQuery($dbname,"pad_surveydt","*","induk='".$notransaksi."'","id asc");
        $resdt = fetchdata($sqldt);

        $sqldtsubi = selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and rincian='-' ","id asc");
        $resdt2sub1 = fetchdata($sqldtsubi);
        $jlhsub1 = count($resdt2sub1);

        $sqldtsubo = selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and rincian !='-' and rincian is not null");
        $resdt2sub2 = fetchdata($sqldtsubo);
        $jlhsub2 = count($resdt2sub2);

        $sqldiscint = selectQuery($dbname,"pad_surveydt2","subjenis","induk='".$resdt[0]['id']."' and rincian !='-' and rincian is not null","id asc",true);
        $ressubjenis2 = fetchdata($sqldiscint);
        $jlhsubjenis2 = count($ressubjenis2);

        $stream.= "<table cellspacing='1' border='1'>";
        $stream.= "<tr>";
            $stream.= "<td align=center>No</td>";
            $stream.= "<td align=center>Menu</td>";
            $stream.= "<td align=center>Sub Menu 1</td>";
            $stream.= "<td align=center>Sub Menu 2</td>";
            $stream.= "<td align=center>Rincian Sub Menu 2</td>";
            if($jlhsub2!=0)
            {
                $stream.= "<td align=center>Rincian Sub Menu 2.1</td>";
                $stream.= "<td align=center>Keterangan Rincian Sub Menu 2.1</td>";
            }
           
            $stream.= "<td align=center>Keterangan</td>";
        $stream.= "</tr>";      
        
        
                $stream.= "<tr>";
                @$no+=1;
                $rospan=0;
                if($jlhsub1>=$jlhsub2)
                {
                    $rospan=$jlhsub1;
                }
                else
                {
                    $rospan=$jlhsub2;
                }
                if($jlhsub2==0)
                {
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$no."</td>";
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$optTipe[$resdt[0]['tipe']]."</td>";
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$optOrg[$resht[0]['kodeorg']]."</td>";
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$resdt[0]['jenis']."</td>";
                    $stream.= "<td>".$resdt2sub1[0]['subjenis']."</td>";
                    $stream.= "<td>".$resdt2sub1[0]['keterangan']."</td>";
                    $stream.= "</tr>";
                    foreach ($resdt2sub1 as $key => $val) {
                        if($key==0){}
                        else
                        {
                            $stream.= "<tr>";
                            $stream.= "<td>".$val['subjenis']."</td>";
                            $stream.= "<td>".$val['keterangan']."</td>";
                            $stream.= "</tr>";
                        }
                        
                    }
                }
                else
                {
                    
                    $datashow = array();
                    $rosw=array();
                    $nos=0;
                    foreach ($resdt2sub1 as $key => $val) {
                       $datashow[$nos]['rinciansubmenu2']=$val['subjenis'];
                       $nos++;
                    }
                    $nos=0;
                    foreach ($ressubjenis2 as $keysub => $valsub) {
                        $sqlsubsub=selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and subjenis='".$valsub['subjenis']."'","id asc");
                        $ressubsub=fetchdata($sqlsubsub);
                        $jlhsubsub=count($ressubsub);
                        if($datashow[$nos]['rinciansubmenu2']=='')
                            {
                                $datashow[$nos]['rinciansubmenu2']='';
                            }
                        $datashow[$nos]['rinciansubmenu21']=$valsub['subjenis'];
                        foreach ($ressubsub as $keysubsub => $valsubsub) {
                            if($keysubsub!=0)
                            {
                                $datashow[$nos]['rinciansubmenu21']='';
                            }

                            $datashow[$nos]['keteranganrincian']=$valsubsub['rincian'];
                            $datashow[$nos]['keterangan']=$valsubsub['keterangan'];
                            $nos++;
                        }
                        $rosw['rowspon'][]=$nos;
                    }

                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$no."</td>";
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$optTipe[$resdt[0]['tipe']]."</td>";
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$optOrg[$resht[0]['kodeorg']]."</td>";
                    $stream.= "<td rowspan=".$rospan." align=center style='vertical-align:middle'>".$resdt[0]['jenis']."</td>";
                    $stream.= "<td>".$datashow[0]['rinciansubmenu2']."</td>";
                    $stream.= "<td>".$datashow[0]['rinciansubmenu21']."</td>";
                    $stream.= "<td>".$datashow[0]['keteranganrincian']."</td>";
                    $stream.= "<td>".$datashow[0]['keterangan']."</td>";
                    $stream.= "</tr>";
                    foreach ($datashow as $keyta => $valen) {
                        if($keyta!=0)
                        {
                            $stream.= "<tr>";
                            foreach ($valen as $keysa => $valsa) {
                                    $stream.= "<td>".$valsa."</td>";
                            }
                            $stream.= "</tr>";
                        }
                    }

                }
                

                      
        
        
        $stream.= "</table>";
        
        $tglSkrg = date("Ymd");
        $nop_ = "Survey_";
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
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
    case 'showupload':
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['nomor']."</td>
                <td>:</td>
                <td>
                    <label id='noupload' style='display:none'>".$data['notransaksi']."</label>
                    <label style='font-weight:bold'>".$data['notransaksi']."</label>
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
        $nmTemp=str_replace('-','',str_replace('/','',$data['notransaksi']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $nmTemp."_".$his."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 250000){
                        $str = "insert into ".$dbname.".listfile_pad_survey values ('','".$data['notransaksi']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
                    }else{
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus .jpg atau .jpeg");
                }
            }
        }
    break;
    case'viewfile':
        $tab="";
        $tab.="<img src='".$path.$data['namafile']."' style='width:600px;height:400px;'>";
        
        echo $tab;
    break;
    
    case 'deletefile':
        $namafile=$data['namafile'];
        $str="delete from ".$dbname.".listfile_pad_survey where nomor='".$data['notransaksi']."' and namafile='".$data['namafile']."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    case'viewlistfile':
        $tab.="<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>
                <table class='sortable' cellspacing='1' border='0' style=min-width:350px>
                    <thead>
                    <tr class=rowheader>
                        <td align='center' width=50px>No.</td>
                        <td align='center' width=50px>File Type</td>
                        <td align='center'>Filename</td>
                        <td align='center' width=50px>Action</td>
                    </tr>
                    </thead>
                    <tbody id='loadfilesdetail'>
                    </tbody>
                </table>
            </fieldset> ";
        echo $tab;
    break;
    
    case 'deletefileall':
        $str="select * from ".$dbname.".listfile_pad_survey where nomor='".$data['notransaksi']."'"; //exit('error'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $pathx = $path.$bar['namafile'];
            unlink($pathx);
        }
        
        $str="delete from ".$dbname.".listfile_pad_survey where nomor='".$data['notransaksi']."'";
        try{
            $owlPDO->exec($str);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    case 'loadfiles':
        $no = 0;
        $tab = "";  
        $str="select * from ".$dbname.".pad_surveyht where notransaksi = '".$data['notransaksi']."'";
        $res=fetchData($str);
        $posting=$res[0]['posting'];
        
        $str="select * from ".$dbname.".listfile_pad_survey where nomor = '".$data['notransaksi']."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                @$icon = seticonfile($val['formaticon']);   
                $tab.="<td style='text-align:center'>
                    <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";

                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                    <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($posting==0){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['nomor']."','".$val['namafile']."');\" >";                 
                }
                
                $tab."  </td>
                </tr>";
            }   
        }
        
        echo $tab;
    break;
     default:
        break;
    }
?>
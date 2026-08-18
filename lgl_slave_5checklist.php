<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

switch($proses) {
    
        case 'add':
        $query = selectQuery($dbname,"lgl_5checklist","kode");
        $kodes = fetchData($query);
        $maxkode=1;
        if(!empty($kodes)) {
        foreach($kodes as $row) {
        $row['kode']>=$maxkode ? $maxkode=$row['kode'] : false;
        }
        $maxkode++;
        }
        $data['kode']=$maxkode;
        #=============== Insert Process
        # Column
        $column = array('kode','jenis','status','createdby','createdtime');
        # Query
        $data['createdby']=$_SESSION['standard']['userid'];
        $data['createdtime']=date("Y-m-d H:i:s");
        $query = insertQuery($dbname,'lgl_5checklist',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['kode'];
        break;
    case 'edit':
        unset($data['kode']);
        $data['updateby']=$_SESSION['standard']['userid'];
        $data['updatetime']=date("Y-m-d H:i:s");
        $query = updateQuery($dbname,'lgl_5checklist',$data,"kode='".$_POST['kode']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        echo json_encode($data);
            
        break;
    case 'addDetail':
        $tab="<div id='divdetail'>";
        //exit($tab);
        //$tab.=OPEN_BOX('','<span class=judul>'.strtoupper($data['kodeheader']."-"$data['kode']).'</span>');
        $query = selectQuery($dbname,"lgl_5checklistdet","kode,kodeheader,noinduk,nourut,deskripsi,tipe","kodeheader='".$data['kodeheader']."' and noinduk='0'");
        $resTab = fetchData($query);
        #== Prep List Header
        $header = array("Kode Header","Nomor Induk","Nomor Urut ","Deskripsi","Tipe");

        $table = "<table id='listData' class='sortable' border=0 style='width:500px;>";
        $table .= "<thead><tr class='rowheader'>";
        foreach($header as $head) {
            $table .= "<td align='center'>".$head."</td>";
        }
        $table .= "<td style='width:30px;' colspan=3 align='center'>*</td>";
        $table .= "</tr></thead>";
        $table .= "<tbody id='bodyListdetail0'>";
        foreach($resTab as $key=>$row) 
        {
            $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
            
            foreach($row as $col=>$dat) 
            {
                if($col != 'kode')
                {
                    if($col == 'tipe')
                    {
                        if($dat==1)
                        {
                            $dat='Panduan';
                        }
                        else
                        {
                            $dat='Pertanyaan';
                        }
                    }
                    if($col == 'deskripsi')
                    {
                        $dat=str_replace('####','<br />',$dat);
                    }
                    $table .= "<td id='".$col."0_".$key."'>".$dat."</td>";
                }
            }
            $table .= "<td id='edit_".$key."'>";
            $table .= "<img src='images/application/application_edit.png' ";
            $table .= "class=resicon  title='Edit' onclick='editdetail(".$key.",0,".$row['kode'].")'></td>";   
            $table .= "<td id='adddetail_".$key."'>";
            $table .= "<img src=images/plus.png ";
            $table .= "class=resicon  title='Add Detail ' onclick='addDetail2(".$key.",event,0,".$row['kode'].")'></td>";
            $table .= "</tr>";
        }
        $table .= "</tbody>";
        $table .= "<tfoot></tfoot></table>";

        #== Prep Form Header

        # Elements
        $els = array();
        
        $els[] = array(
            makeElement('kodeheader0','label','Kode Header'),
            makeElement('kodeheader0','textnum',$data['kodeheader'],array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
        );

        $els[] = array(
            makeElement('noinduk0','label','No. Induk'),
            makeElement('noinduk0','textnum',0,array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
        );
        $els[] = array(
            makeElement('nourut0','label','No. Urut'),
            makeElement('nourut0','textnum',0,array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)'))
        );
        $els[] = array(
            makeElement('tipe0','label','Pertanyaan/Panduan'),
            makeElement('tipe0','check',0)
        );
        $els[] = array(
            makeElement('deskripsi0','label','Deskripsi'),
            makeElement('deskripsi0','textarea','',array('style'=>'width:145px',
                'onkeypress'=>'return tanpa_kutip(event)'))
        );
    
        $els2['btn'] = array(
            makeElement('saveButton0','button',$_SESSION['lang']['save'],array('onclick'=>'addDataDetail(0)')),
            makeElement('clearButton0','button',$_SESSION['lang']['cancel'],array('onclick'=>'clearDataDetail(0)'))
        );

        //echo "<pre>";
        //print_r($_SESSION);
        //echo "</pre>";
        #===== Show =======


        # Active Form
        $tab.= "<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
        $tab.= genElement($els);
        $tab.= genElement($els2);
        $tab.= "</fieldset>";
        $tab.="</div>";
        //$tab.=CLOSE_BOX();

        //$tab.=OPEN_BOX();
        $tab.="<div id='divdetaillist'>";
        # Table
        $tab.= "<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>";
        $tab.= "<div id=containerdetail>";
        $tab.= $table;
        $tab.= "</div>";
        $tab.= "</fieldset>";
        $tab.="</div>";
        //tab.=CLOSE_BOX();

        echo $tab;
        break;
        case 'addDetail2':
        $tab="<div id='divdetail2'>";
        //exit($tab);
        //$tab.=OPEN_BOX('','<span class=judul>'.strtoupper($data['kodeheader']."-"$data['kode']).'</span>');

        $query = selectQuery($dbname,"lgl_5checklistdet","kode,kodeheader,noinduk,nourut,deskripsi,tipe","kodeheader='".$data['kodeheader']."' and noinduk='".$data['kode']."'");
        $resTab = fetchData($query);
        #== Prep List Header
        $header = array("Kode Header","Nomor Induk","Nomor Urut ","Deskripsi","Tipe");

        $table = "<table id='listData2' class='sortable' border=0 style='width:500px;>";
        $table .= "<thead><tr class='rowheader'>";
        foreach($header as $head) {
            $table .= "<td align='center'>".$head."</td>";
        }
        $table .= "<td style='width:30px;' colspan=3 align='center'>*</td>";
        $table .= "</tr></thead>";
        $table .= "<tbody id='bodyListdetail2'>";
        foreach($resTab as $key=>$row) 
        {
            $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
            
            foreach($row as $col=>$dat) 
            {
                if($col != 'kode')
                {
                    if($col == 'tipe')
                    {
                        if($dat==1)
                        {
                            $dat='Panduan';
                        }
                        else
                        {
                            $dat='Pertanyaan';
                        }
                    }
                    if($col == 'deskripsi')
                    {
                        $dat=str_replace('####','<br />',$dat);
                    }
                    $table .= "<td id='".$col."2_".$key."'>".$dat."</td>";
                }
            }
            $table .= "<td id='edit_".$key."'>";
            $table .= "<img src='images/application/application_edit.png' ";
            $table .= "class=resicon  title='Edit' onclick='editdetail(".$key.",2,".$row['kode'].")'></td>";   
            $table .= "<td id='adddetail_".$key."'>";
            $table .= "<img src=images/plus.png ";
            $table .= "class=resicon  title='Add Detail ' onclick='addDetail3(".$key.",event,2,".$row['kode'].")'></td>";
            $table .= "</tr>";
        }
        $table .= "</tbody>";
        $table .= "<tfoot></tfoot></table>";

        #== Prep Form Header

        # Elements
        $els = array();
        $els[] = array(
            makeElement('kodeheader2','label','Kode Header'),
            makeElement('kodeheader2','textnum',$data['kodeheader'],array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
        );
        $els[] = array(
            makeElement('noinduk2','label','No. Induk'),
            makeElement('noinduk2','textnum',$data['kode'],array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
        );
        $els[] = array(
            makeElement('nourut2','label','No. Urut'),
            makeElement('nourut2','textnum','0',array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)'))
        );
        
        $els[] = array(
            makeElement('tipe2','label','Pertanyaan/Panduan'),
            makeElement('tipe2','check',0)
        );
        $els[] = array(
            makeElement('deskripsi2','label','Deskripsi'),
            makeElement('deskripsi2','textarea','',array('style'=>'width:145px',
                'onkeypress'=>'return tanpa_kutip(event)'))
        );
    
        $els2['btn'] = array(
            makeElement('saveButton2','button',$_SESSION['lang']['save'],array('onclick'=>'addDataDetail(2)')),
            makeElement('clearButton2','button',$_SESSION['lang']['cancel'],array('onclick'=>'clearDataDetail(2)'))
        );

        //echo "<pre>";
        //print_r($_SESSION);
        //echo "</pre>";
        #===== Show =======


        # Active Form
        $tab.= "<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
        $tab.= genElement($els);
        $tab.= genElement($els2);
        $tab.= "</fieldset>";
        $tab.="</div>";
        //$tab.=CLOSE_BOX();

        //$tab.=OPEN_BOX();
        $tab.="<div id='divdetaillist2'>";
        # Table
        $tab.= "<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>";
        $tab.= "<div id=containerdetail2>";
        $tab.= $table;
        $tab.= "</div>";
        $tab.= "</fieldset>";
        $tab.="</div>";
        //tab.=CLOSE_BOX();

        echo $tab;
        break;
        case 'addDetail3':
        $tab="<div id='divdetail3'>";
        //exit($tab);
        //$tab.=OPEN_BOX('','<span class=judul>'.strtoupper($data['kodeheader']."-"$data['kode']).'</span>');
        $query = selectQuery($dbname,"lgl_5checklistdet","kode,kodeheader,noinduk,nourut,deskripsi,tipe","kodeheader='".$data['kodeheader']."' and noinduk='".$data['kode']."'");
        $resTab = fetchData($query);
        #== Prep List Header
        $header = array("Kode Header","Nomor Induk","Nomor Urut ","Deskripsi","Tipe");

        $table = "<table id='listData' class='sortable' border=0 style='width:500px;>";
        $table .= "<thead><tr class='rowheader'>";
        foreach($header as $head) {
            $table .= "<td align='center'>".$head."</td>";
        }
        $table .= "<td style='width:30px;' colspan=3 align='center'>*</td>";
        $table .= "</tr></thead>";
        $table .= "<tbody id='bodyListdetail3'>";
        foreach($resTab as $key=>$row) 
        {
            $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
            
            foreach($row as $col=>$dat) 
            {
                if($col != 'kode')
                {
                    if($col == 'tipe')
                    {
                        if($dat==1)
                        {
                            $dat='Panduan';
                        }
                        else
                        {
                            $dat='Pertanyaan';
                        }
                    }
                    if($col == 'deskripsi')
                    {
                        $dat=str_replace('####','<br />',$dat);
                    }
                    $table .= "<td id='".$col."3_".$key."'>".$dat."</td>";
                }
            }
            $table .= "<td id='edit_".$key."'>";
            $table .= "<img src='images/application/application_edit.png' ";
            $table .= "class=resicon  title='Edit' onclick='editdetail(".$key.",3,".$row['kode'].")'></td>";   
            $table .= "<td id='adddetail_".$key."'>";
            $table .= "<img src=images/plus.png ";
            $table .= "class=resicon  title='Add Detail ' onclick='addDetail2(".$key.",event,3,".$row['kode'].")'></td>";
            $table .= "</tr>";
        }
        $table .= "</tbody>";
        $table .= "<tfoot></tfoot></table>";

        #== Prep Form Header

        # Elements
        $els = array();
        $els[] = array(
            makeElement('kodeheader3','label','Kode Header'),
            makeElement('kodeheader3','textnum',$data['kodeheader'],array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
        );
        $els[] = array(
            makeElement('noinduk3','label','No. Induk'),
            makeElement('noinduk3','textnum',$data['kode'],array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
        );
        $els[] = array(
            makeElement('nourut3','label','No. Urut'),
            makeElement('nourut3','textnum','0',array('style'=>'width:145px','maxlength'=>'20',
                'onkeypress'=>'return tanpa_kutip(event)'))
        );
        $els[] = array(
            makeElement('tipe3','label','Pertanyaan/Panduan'),
            makeElement('tipe3','check',0)
        );
        $els[] = array(
            makeElement('deskripsi3','label','Deskripsi'),
            makeElement('deskripsi3','textarea','',array('style'=>'width:145px',
                'onkeypress'=>'return tanpa_kutip(event)'))
        );
    
        $els2['btn'] = array(
            makeElement('saveButton3','button',$_SESSION['lang']['save'],array('onclick'=>'addDataDetail(3)')),
            makeElement('clearButton3','button',$_SESSION['lang']['cancel'],array('onclick'=>'clearDataDetail(3)'))
        );

        //echo "<pre>";
        //print_r($_SESSION);
        //echo "</pre>";
        #===== Show =======


        # Active Form
        $tab.= "<fieldset id='fieldForm' style='width:500px; clear:right;min-height:auto;'>";
        $tab.= genElement($els);
        $tab.= genElement($els2);
        $tab.= "</fieldset>";
        $tab.="</div>";
        //$tab.=CLOSE_BOX();

        //$tab.=OPEN_BOX();
        $tab.="<div id='divdetaillist'>";
        # Table
        $tab.= "<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>";
        $tab.= "<div id=containerdetail>";
        $tab.= $table;
        $tab.= "</div>";
        $tab.= "</fieldset>";
        $tab.="</div>";
        //tab.=CLOSE_BOX();

        echo $tab;
        break;
        case 'addDataDetail':

        $query = selectQuery($dbname,"lgl_5checklistdet","kode");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['kode']>=$maxid ? $maxid=$row['kode'] : false;
        }
        $maxid++;
        }
        $data['kode']=$maxid;

        $eee=explode("\n",$data['deskripsi']);
        $no='';
        foreach($eee as $ggg => $hhh){
            $no+=1;
            if($no < count($eee)){
                @$ghjkl.=trim($hhh)."####";
            }else{
                @$ghjkl.=trim($hhh);
            }
        }
        $data['deskripsi'] = $ghjkl;
        
        $column = array('kode','nourut','noinduk','kodeheader','deskripsi','tipe');
        # Query
        
        $query = insertQuery($dbname,'lgl_5checklistdet',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['kode'];
        break;
    case 'editdetail':
        unset($data['kode']);
        
        $eee=explode("\n",$data['deskripsi']);
        
        $no='';
        foreach($eee as $ggg => $hhh){
            $no+=1;
            if($no < count($eee)){
                @$ghjkl.=trim($hhh)."####";
            }else{
                @$ghjkl.=trim($hhh);
            }
        }
        $data['deskripsi'] = $ghjkl;

        $query = updateQuery($dbname,'lgl_5checklistdet',$data,"kode='".$_POST['kode']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        echo json_encode($data);
            
        break;
    case 'getdetail':
        $optjenis = makeOption($dbname,"lgl_5checklist","kode,jenis","status=1 and kode='".$data['kodeheader']."'");
        $formdet ="<fieldset id='fieldFormDet' style='width:100%; clear:right;min-height:auto;'>";
        $formdet .="<legend>Form Checklist : ".$optjenis[$data['kodeheader']]."</legend>";
        $formdet .= "<table cellspacing='0' border='1' >";
        $formdet .= "<tr>";
        $formdet .= "<td>Dokumen Referensi</td>";
        $formdet .= "<td>Pertanyaan</td>";
        if($optjenis[$data['kodeheader']]!='ISPO'){
        $formdet .= "<td>Panduan</td>";}
        if($optjenis[$data['kodeheader']]=='ISPO'){
        $formdet .= "<td></td>";
        $formdet .= "<td>Kriteria</td>";
        $formdet .= "<td></td>";
        $formdet .= "<td>Indikator</td>";
        $formdet .= "<td>Panduan</td>";
        }
        $formdet .= "</tr>";
        
        $str="select * from lgl_5checklistdet where kodeheader='".$data['kodeheader']."' and tipe=1 order by nourut";    
        $res=fetchData($str);
        $deskripsi=array();
        foreach ($res as $key => $val) {
            $deskripsi[$val['noinduk']]=$val['deskripsi'];
        }

        $str="select min(kode) as minnourut, max(kode) as maxnourut from lgl_5checklistdet where kodeheader='".$data['kodeheader']."' and tipe=0 ";    
        $res=fetchData($str);
        $maxnourut=$res[0]['maxnourut'];
        $minnourut=$res[0]['minnourut'];

        /*print_r($deskripsi);
        exit();*/
        $str0="select * from lgl_5checklistdet where kodeheader='".$data['kodeheader']."' and noinduk=0 and tipe=0 order by nourut"; 
        $res0=fetchData($str0);
        foreach ($res0 as $key0 => $value0) 
        {
                
                $formdet .= "<tr>";
                $formdet .= "<td>".$value0['nourut']."</td>";
                $formdet .= "<td>".str_replace('####','<br />',$value0['deskripsi'])."</td>";
                if($optjenis[$data['kodeheader']]=='SMK3'){
                    if($deskripsi[$value0['kode']]=='')
                    {
                        $formdet .= "<td></td>";
                    }
                    else
                    {
                        $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                    }
                }
                elseif($optjenis[$data['kodeheader']]=='Permohonan HGU' || $optjenis[$data['kodeheader']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['kodeheader']]=='Proper' )
                {
                    if($deskripsi[$value0['kode']]=='')
                    {
                        $formdet .= "<td></td>";
                    }
                    else
                    {
                        $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value0['kode']])."</td>";
                    }
                }
                if($optjenis[$data['kodeheader']]!='ISPO'){
                $formdet .= "</tr>";}
                $str1="select * from lgl_5checklistdet where kodeheader='".$data['kodeheader']."' and noinduk='".$value0['kode']."' and tipe=0 order by nourut";    
                $res1=fetchData($str1);
                foreach ($res1 as $key1 => $value1)
                {
                    if($optjenis[$data['kodeheader']]!='ISPO'){
                        $formdet .= "<tr>";}
                    if($optjenis[$data['kodeheader']]=='ISPO' && $key1>0){
                        $formdet .= "<tr><td></td><td></td>";}
                    $formdet .= "<td>".$value0['nourut'].".".$value1['nourut']."</td>";
                    $formdet .= "<td>".str_replace('####','<br />',$value1['deskripsi'])."</td>";
                    if($optjenis[$data['kodeheader']]=='SMK3'){
                        if($deskripsi[$value1['kode']]=='')
                        {
                            $formdet .= "<td></td>";
                        }
                        else
                        {
                            $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                            
                        }
                    }
                    elseif($optjenis[$data['kodeheader']]=='Permohonan HGU' || $optjenis[$data['kodeheader']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['kodeheader']]=='Proper' )
                            {
                                if($deskripsi[$value0['kode']]=='')
                                {
                                    $formdet .= "<td></td>";
                                }
                                else
                                {
                                    $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                                }
                            }
                    if($optjenis[$data['kodeheader']]!='ISPO'){
                    $formdet .= "</tr>";}
                    $str2="select * from lgl_5checklistdet where kodeheader='".$data['kodeheader']."' and noinduk='".$value1['kode']."' and tipe=0 order by nourut";    
                    $res2=fetchData($str2);
                    foreach ($res2 as $key2 => $value2)
                    {
                        if($optjenis[$data['kodeheader']]!='ISPO'){
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
                        if($optjenis[$data['kodeheader']]=='SMK3' || $optjenis[$data['kodeheader']]=='ISPO'){
                            if($deskripsi[$value2['kode']]=='')
                            {
                                
                                if($optjenis[$data['kodeheader']]=='ISPO')
                                {   
                                    $formdet .= "<td></td>";
                                    
                                    $formdet .="</tr>";
                                }
                                else
                                {
                                    $formdet .= "<td></td>";
                                }
                            }
                            else
                            {
                                $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                                
                            }
                        }
                        elseif($optjenis[$data['kodeheader']]=='Permohonan HGU' || $optjenis[$data['kodeheader']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['kodeheader']]=='Proper' )
                            {
                                $formdet .= "<td></td>";
                            }
                        if($optjenis[$data['kodeheader']]!='ISPO'){
                        $formdet .= "</tr>";}
                        $str3="select * from lgl_5checklistdet where kodeheader='".$data['kodeheader']."' and noinduk='".$value2['kode']."' and tipe=0 order by nourut";    
                        $res3=fetchData($str3);
                        foreach ($res3 as $key3 => $value3)
                        {
                            if($optjenis[$data['kodeheader']]!='ISPO'){
                            $formdet .= "<tr>";}
                            $formdet .= "<td>".$value0['nourut'].".".$value1['nourut'].".".$value2['nourut'].".".$value3['nourut']."</td>";
                            $formdet .= "<td>".str_replace('####','<br />',$value3['deskripsi'])."</td>";
                            if($optjenis[$data['kodeheader']]=='SMK3'){
                                if($deskripsi[$value3['kode']]=='')
                                {
                                    $formdet .= "<td></td>";
                                }
                                else
                                {
                                    $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                                    
                                }
                            }
                            elseif($optjenis[$data['kodeheader']]=='Permohonan HGU' || $optjenis[$data['kodeheader']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['kodeheader']]=='Proper' )
                            {
                                $formdet .= "<td></td>";
                            }
                        }

                            
                    }
                }
          $formdet .= "</tr>";  
        }

       
        $formdet .= "</table>";
        $formdet .= "</fieldset>";
        echo $formdet;

        break;
     default:
        break;
    }
?>
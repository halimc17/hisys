<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$idOrg=substr($_SESSION['empl']['kodeorganisasi'],0,4);
$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$idOrg."'",'2',true);
$loksrc = checkPostGet('loksrc', '');
$peksrc = checkPostGet('peksrc', '');
$mgsrc = checkPostGet('mgsrc', '');
$tglawal = tanggalsystem(checkPostGet('tglawal', ''));
$tglakhir = tanggalsystem(checkPostGet('tglakhir', ''));

switch($proses) {
    case 'loadData' :
    $where='';
	if($loksrc!=''){
		$where.=" and a.kodeorg='".$loksrc."'";		
	}
	if($peksrc!=''){
		$where.=" and kodeproject in (select kode from ".$dbname.".project where nama like '%".$peksrc."%')";		
	}
	if($tglawal!=''){
		$where.=" and tanggalawal='".$tglawal."'";		
	}
	if($tglakhir!=''){
		$where.=" and tanggalakhir='".$tglakhir."'";		
	}
	if($mgsrc!=''){
		$where.=" and mingguke='".$mgsrc."'";		
	}
	
    $limit = 20;
    $page = 0;
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        if ($page < 0)
            $page = 0;
    }
    $offset = $page * $limit;

    $qcount ="select a.kodeorg, b.namaorganisasi, a.kodeproject, a.mingguke, a.tanggalawal, a.tanggalakhir from ".$dbname.".vhc_progproject a ";
    $qcount .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    $qcount .="WHERE b.induk='".$idOrg."' ".$where." and b.tipe in ('KEBUN','PABRIK')";
    $qcount .="order by `mingguke` desc";
    $rcount = fetchData($qcount);
    $jlhbrs = count($rcount);


    $queryAll ="select a.kode,a.kodeorg, b.namaorganisasi, a.kodeproject, c.nama, a.mingguke, a.tanggalawal, a.tanggalakhir,a.posting from ".$dbname.".vhc_progproject a ";
    $queryAll .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    $queryAll .="LEFT JOIN ".$dbname.".project c on a.kodeproject=c.kode ";
    $queryAll .="WHERE b.induk='".$idOrg."' ".$where." and b.tipe in ('KEBUN','PABRIK')";
    $queryAll .="order by `mingguke` desc limit " . $offset . "," . $limit . "";
    $resAll = fetchData($queryAll);

    
    $table='';
    foreach($resAll as $key => $row) {
    	$table.="<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
		$no+=1;
		$table.="<td align=center>".$no."</td>";
    	foreach($row as $col => $dat) {
    		if ($col == 'kode' || $col == 'kodeorg' || $col == 'kodeproject') {
    			$table.="<td hidden id='".$col."_".$key."'>".$dat."</td>";
    		}
    		elseif($col == 'posting') {}
    		else {
    			if ($col == 'tanggalawal' || $col == 'tanggalakhir') {
    				$dat = tanggalnormal($dat);
    			}
    			$table.="<td id='".$col."_".$key."'>".$dat."</td>";
    		}
    	}
    	if ($row['posting'] == 0) {
    		$table.="<td id='edit_".$key."'>";
    		$table.="<img src='images/application/application_edit.png' ";
    		$table.="class=resicon  caption='Edit' onclick='edit(".$key.")'></td>";
    		$table.="<td id='pdf_".$key."'>";
    		$table.="<img src='images/pdf.jpg' ";
    		$table.="class=resicon  caption='Print' onclick=\"masterPDF('vhc_progproject','".$row['kode'].",".$row['kodeorg'].",".$row['kodeproject'].",".$row['mingguke'].",".$row['tanggalawal'].",".$row['tanggalakhir']."','','vhc_slave_progprojectpdf',event)\"></td>";

    		$table.="<td align='center'>";
    		$table.="<a href='#' onclick=dataKeExcel(event,'vhc_slave_progproject.php','".$row['kode'].",".$row['kodeorg'].",".$row['kodeproject'].",".$row['mingguke'].",".$row['tanggalawal'].",".$row['tanggalakhir']."') ><img  src=images/excel.jpg class=resicon title='MS.Excel'>";
    		$table.="</a></td>";
    		$table.="<td><img src='images/skyblue/posting.png' class='resicon' title='posting' onclick=postIni('".$key."')></td>";
    		$table.="<td id='delete_".$key."'>";
    		$table.="<img src='images/application/application_delete.png' ";
    		$table.="class=resicon  caption='Delete' onclick='deletehd(".$key.")'></td>";
    	} else {
    		$table.="<td id='edit_".$key."'></td>";
    		$table.="<td id='pdf_".$key."'>";
    		$table.="<img src='images/pdf.jpg' ";
    		$table.="class=resicon  caption='Print' onclick=\"masterPDF('vhc_progproject','".$row['kode'].",".$row['kodeorg'].",".$row['kodeproject'].",".$row['mingguke'].",".$row['tanggalawal'].",".$row['tanggalakhir']."','','vhc_slave_progprojectpdf',event)\"></td>";
    		$table.="<td align='center'>";
    		$table.="<a href='#' onclick=dataKeExcel(event,'vhc_slave_progproject.php','".$row['kode'].",".$row['kodeorg'].",".$row['kodeproject'].",".$row['mingguke'].",".$row['tanggalawal'].",".$row['tanggalakhir']."') ><img  src=images/excel.jpg class=resicon title='MS.Excel'>";
    		$table.="</a></td>";
    		$table.="<td><img src='images/skyblue/posted.png' class='resicon' title='posted'</td>";
    		$table.="<td id='delete_".$key."'></td>";
    	}

    	$table.="</tr>";

    }
    $tablex ="<tr class=rowheader><td colspan=11 align=center>" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
    <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
    <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
    </td>
    </tr>";

    echo $table.'###'.$tablex;
    break;
    
    case 'checkHeader':

    if(date('Ymd',strtotime($data['tanggalawal']))>date('Ymd',strtotime($data['tanggalakhir'])))
    {
        exit('Warning : Tanggal awal tidak bisa lebih besar dari tanggal akhir');
    }

    $query=selectQuery($dbname,"vhc_progproject","mingguke,tanggalawal,tanggalakhir,posting","kodeorg='".$data['kodeorg']."' and kodeproject='".$data['kodeproject']."'");
    $res=fetchData($query);
    $jlmh=count($res);
    
    if($jlmh!=0)
    {   
    //exit('ERROR : '.$query);
        $tglx=0;
        $tgly=0;
        $mgx=0;
        $postx=0;
        foreach ($res as $key => $val) {
            //echo "Error :".date('Ymd',strtotime($data['tanggalawal']));
            //exit();
            $tgl1=date('Ymd',strtotime($data['tanggalawal']));
            $tgl2=date('Ymd',strtotime($data['tanggalakhir']));
            $tgl1x=date('Ymd',strtotime($val['tanggalawal']));
            $tgl2x=date('Ymd',strtotime($val['tanggalakhir']));
            if(($tgl1>=$tgl1x) and ($tgl1<=$tgl2x))
            {
                $tglx=1;
            }
            if(($tgl2>=$tgl1x) and ($tgl2<=$tgl2x))
            {
                $tgly=1;
            }
            if($data['minggu']==$val['mingguke'])
            {
                $mgx=1;
            }
            if($val['posting']==0)
            {
                $postx=1;
            }
        }
        if($mgx==1)
        {
            exit("Warning : Data dengan mingguke : ".$data['minggu']." sudah ada");
        }
        if($tglx==1)
        {
            exit("Warning : Data dengan tanggal awal sudah ada ");
        }
        if($tgly==1)
        {
            exit("Warning : Data dengan tanggal akhir sudah ada ");
        }
        if($postx==1)
        {
            exit("Warning : Ada data yang belum di posting dengan project yang sama");
        }
    }
    
    break;
    case 'getproject':
    $query=selectQuery($dbname,"project","kode,nama","kode like '%BG%' and kodeorg='".$data['kodeorg']."' and posting='0'");
    $res=fetchData($query);
    $optProj="<option value='' >".$_SESSION['lang']['pilih']."</option>";
    
    foreach ($res as $key => $val) {
        $optProj.="<option value='".$val['kode']."'>".$val['nama']."</option>";
    }

    echo $optProj;
    break;
    case 'posting':
            $datax=array('posting'=>'1');
            $qdetInsz = updateQuery($dbname,"vhc_progproject",$datax,"kode='".$data['kode']."' and kodeorg='".$data['kodeorg']."' and kodeproject='".$data['kodeproject']."'");
            //exit('Error :'.$qdetInsz);
            try{
                $owlPDO->exec($qdetInsz); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }

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
        $where = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."')  and statuskaryawan != 'Keluar' and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
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
            $formdet .= "<td align=center>".makeElement('shift_'.$data['nomer'].'_'.intval($value),'select','',array('style'=>'width:75px'),$optShift)."</td>";
            $btsatgl=intval($value);
        }
        $formdet .="</tr>";

        echo $formdet;


    break;
    case 'getDetail':
    $arrdat='';
    $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$data['kodeproject']."' group by deskripsi order by deskripsi";
    $res=fetchData($query);
    
    $formdet ="<fieldset id='fieldFormDet' clear:right;min-height:auto;'>";
    $formdet .="<legend>Rincian</legend>";
    $formdet .= "<table cellspacing='1' border='0' class='sortable'>";
    $formdet .= "<thead><tr class='rowheader'>";
    $formdet .= "<td>Pekerjaan</td>";
    $formdet .= "<td>Volume</td>";
    $formdet .= "<td>Satuan</td>";
    $formdet .= "<td>Bobot</td>";
    $formdet .= "</tr>";
    $formdet .="</thead>";
    $formdet .= "<tbody id='formListdetailx'>";
    foreach ($res as $key => $val) {
        $formdet .= "<tr id='tr_x' class='rowcontent' style='cursor:pointer;background-color:#dcdcdc;'>";
        $formdet .= "<td>".$val['deskripsi']."</td>";
        $formdet .= "<td></td>";
        $formdet .= "<td></td>";
        $formdet .= "<td></td>";
        $formdet .= "</tr>";
        $query2="select * from ".$dbname.".project_dt  where kodeproject='".$val['kodeproject']."' and deskripsi='".$val['deskripsi']."'";
        $res2=fetchData($query2);
            foreach ($res2 as $key2 => $val2) {
                $arrdat.='###'.$val2['kegiatan'].'/'.$key2.'/'.$val2['volume_berjalan'].'/'.$val2['bobot_berjalan'];
                $formdet .= "<tr id='tr_".$val2['kegiatan']."_".$key2."' class='rowcontent' style='cursor:pointer'>";
                $formdet .= "<td hidden id='induk_".$val2['kegiatan']."_".$key2."'>".$data['induk']."</td>";
                $formdet .= "<td hidden id='kegiatan_".$val2['kegiatan']."_".$key2."'>".$val2['kegiatan']."</td>";
                $formdet .= "<td>".$val2['namakegiatan']."</td>";
                if($val2['volume']==$val2['volume_berjalan'])
                {
                    $formdet .= "<td>".makeElement('volume_'.$val2['kegiatan'].'_'.$key2,'textnum','0',array('style'=>'width:100px;','disabled'=>'disabled'))."</td>";
                }
                else
                {
                    $formdet .= "<td>".makeElement('volume_'.$val2['kegiatan'].'_'.$key2,'textnum','0',array('style'=>'width:100px;','onblur'=>"getbobot('".$val2['kegiatan']."','".$key2."','".$val2['volume']."','".$val2['volume_berjalan']."','".$val2['bobot']."','".$val2['bobot_berjalan']."')"))."</td>";
                }
                $formdet .= "<td>".$val2['satuan']."</td>";
                $formdet .= "<td>".makeElement('bobot_'.$val2['kegiatan'].'_'.$key2,'textnum','0',array('style'=>'width:100px;','disabled'=>'disabled'))."</td>";
                $formdet .= "</tr>";
            } 
    }
    $formdet .= "</tbody>";
    $formdet .= "</table>";
    $formdet .=  makeElement('saveDetailButton','button',$_SESSION['lang']['save'],array('onclick'=>"saveData('".$arrdat."')"));

    $formdet .= "</fieldset>";
    echo $formdet;
    
    break;
    case 'getEditDetail':
    
    $formdet ="<fieldset id='fieldFormDet' clear:right;min-height:auto;'>";
    $formdet .="<legend>Rincian</legend>";
    $formdet .= "<table cellspacing='1' border='0' class='sortable'>";
    $formdet .= "<thead><tr class='rowheader'>";
    $formdet .= "<td>Pekerjaan</td>";
    $formdet .= "<td>Volume</td>";
    $formdet .= "<td>Satuan</td>";
    $formdet .= "<td>Bobot</td>";
    $formdet .= "<td>Action</td>";
    $formdet .= "</tr>";
    $formdet .="</thead>";
    $formdet .= "</thead>";
    $formdet .= "<tbody id='formListdetail'>";
    $formdet .= "<tr class='rowcontent' style='cursor:pointer'>";
    $formdet .= "<td id=id_x_y hidden></td>";
    $formdet .= "<td id=induk_x_y hidden></td>";
    $formdet .= "<td id=kegiatan_x_y hidden></td>";
    $formdet .= "<td id=nmkegiatan_x_y ></td>";
    $formdet .= "<td id=volumetotal_x_y hidden></td>";
    $formdet .= "<td id=volumebjln_x_y hidden></td>";
    $formdet .= "<td id=bobottotal_x_y hidden></td>";
    $formdet .= "<td id=bobotbjln_x_y hidden></td>";

    $formdet .= "<td id=volumelalu_x_y hidden></td>";
    $formdet .= "<td id=bobotlalu_x_y hidden></td>";

    $formdet .= "<td>".makeElement('volume_x_y','textnum','0',array('style'=>'width:100px;','onblur'=>'getbobot2()'))."</td>";
    $formdet .= "<td id=satuan_x_y></td>";
    $formdet .= "<td>".makeElement('bobot_x_y','textnum','0',array('style'=>'width:100px;','disabled'=>'disabled'))."</td>";
    $formdet .= "<td>".makeElement('updateDetailButton','button',$_SESSION['lang']['save'],'')."</td>";
    $formdet .="</tr>";
    $formdet .= "</tbody>";
    $formdet .= "</table>";
    $formdet .= "</fieldset>";
    echo $formdet;
    break;
    case 'loadDataDetail':
    
    $arrdat='';
    $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$data['kodeproject']."' group by deskripsi order by deskripsi";
    $res=fetchData($query);
    
    $formdet ="<fieldset id='fieldFormDet' clear:right;min-height:auto;'>";
    $formdet .="<legend>Rincian</legend>";
    $formdet .= "<table cellspacing='1' border='0' class='sortable'>";
    $formdet .= "<thead><tr class='rowheader'>";
    $formdet .= "<td>Pekerjaan</td>";
    $formdet .= "<td>Volume</td>";
    $formdet .= "<td>Satuan</td>";
    $formdet .= "<td>Bobot</td>";
    $formdet .= "<td>Action</td>";
    $formdet .= "</tr>";
    $formdet .="</thead>";
    $formdet .= "<tbody id='formListdetailx'>";
    foreach ($res as $key => $val) {
        $formdet .= "<tr id='tr_x' class='rowcontent' style='cursor:pointer;background-color:#dcdcdc;'>";
        $formdet .= "<td>".$val['deskripsi']."</td>";
        $formdet .= "<td></td>";
        $formdet .= "<td></td>";
        $formdet .= "<td></td>";
        $formdet .= "<td></td>";
        $formdet .= "</tr>";
        $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id from ".$dbname.".project_dt a 
        left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan  where a.kodeproject='".$val['kodeproject']."' and a.deskripsi='".$val['deskripsi']."' and  b.induk='".$data['kode']."'";
        //exit($query2);
        $res2=fetchData($query2);
            foreach ($res2 as $key2 => $val2) {
                $arrdat=$val2['kegiatan'].'###'.$key2.'###'.$val2['volume_berjalan'].'###'.$val2['bobot_berjalan'].'###'.$val2['volume'].'###'.$val2['bobot'];
                $formdet .= "<tr id='tr_".$val2['kegiatan']."_".$key2."' class='rowcontent' style='cursor:pointer'>";
                $formdet .= "<td hidden id='id_".$val2['kegiatan']."_".$key2."'>".$val2['id']."</td>";
                $formdet .= "<td hidden id='induk_".$val2['kegiatan']."_".$key2."'>".$data['kode']."</td>";
                $formdet .= "<td hidden id='kegiatan_".$val2['kegiatan']."_".$key2."'>".$val2['kegiatan']."</td>";
                $formdet .= "<td id='nmkegiatan_".$val2['kegiatan']."_".$key2."'>".$val2['namakegiatan']."</td>";
                $formdet .= "<td id='volume_".$val2['kegiatan']."_".$key2."' align=right>".$val2['volumex']."</td>";
                $formdet .= "<td id='satuan_".$val2['kegiatan']."_".$key2."'>".$val2['satuan']."</td>";
                $formdet .= "<td id='bobot_".$val2['kegiatan']."_".$key2."' align=right>".$val2['bobotx']."</td>";
                $formdet .= "<td id='edit_".$val2['kegiatan']."_".$key2."' align=center>";
                $formdet .= "<img src='images/application/application_edit.png' ";
                $formdet .= "class=resicon  caption='Edit' onclick=editDetail('".$arrdat."')></td>";
                $formdet .= "</tr>";
            } 
    }
    $formdet .= "</tbody>";
    $formdet .= "</table>";

    $formdet .= "</fieldset>";
    echo $formdet;
    break;
    case 'saveData':
        $query = selectQuery($dbname,"vhc_progproject","kode");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['kode']>=$maxid ? $maxid=$row['kode'] : false;
        }
        $maxid++;
        }
        //$konter = addZero(intval($maxid),3);
        $datahd=array();
        $datahd['kode']=$maxid;
        $datahd['kodeorg']=$data['kodeorg'];
        $datahd['kodeproject']=$data['kodeproject'];
        $datahd['mingguke']=$data['minggu'];
        $datahd['tanggalawal']=tanggalsystem($data['tanggalawal']);
        $datahd['tanggalakhir']=tanggalsystem($data['tanggalakhir']);
        $datahd['posting']=0;


        $qIns = insertQuery($dbname,'vhc_progproject',$datahd);
        //exit("Error : ".$qIns);
        try{
            $owlPDO->exec($qIns); 

            $datains=array();
            $dataup=array();
            $datains = explode('###', $data['dataray']);
            $dataup = explode('###', $data['uparay']);
            
            foreach ($datains as $key => $val) {
                if($key==0){}
                else{
                $query = selectQuery($dbname,"vhc_progproject_dt","id");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                foreach($id as $row) {
                $row['id']>=$maxid ? $maxid=$row['id'] : false;
                }
                $maxid++;
                }

                $datainsdt=array();
                $datainsdt=explode('/', $val);
                
                $datadt=array();
                $datadt['id']=$maxid;
                $datadt['induk']=$datahd['kode'];
                $datadt['kegiatan']=$datainsdt[1];
                $datadt['volume']=$datainsdt[2];
                $datadt['bobot']=$datainsdt[3];

                

                    $qdetIns = insertQuery($dbname,'vhc_progproject_dt',$datadt);
                    try{
                        $owlPDO->exec($qdetIns); 
                        }
                    catch (PDOException $e) 
                        {
                        print " Gagal insert data proses project !: " . $qdetIns . "\n"; die(); 
                        }
                }
                
            }

                
            foreach ($dataup as $key => $val) {
                if($key==0)
                {}
                else{
                $dataupdt=array();
                $dataupdt=explode('/', $val);
                
                $datadt=array();
                $datadt['volume_berjalan']=floatval($dataupdt[2]);
                $datadt['bobot_berjalan']=floatval($dataupdt[3]);
                    /*echo"<pre>";
                    print_r($datadt);
                    echo"</pre>";*/

                $qdetIns = updateQuery($dbname,"project_dt",$datadt,"kegiatan='".intval($dataupdt[0])."' and kodeproject='".$dataupdt[1]."'");
                try{
                    $owlPDO->exec($qdetIns); 
                    }
                catch (PDOException $e) 
                    {
                    print " Gagal update project volume dan bobot berjalan !: " . $e->getMessage() . "\n"; die(); 
                    }
                }
            }
     }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
        }
    break;
    case 'updateData':

            $datadt=array();
            $datadt['volume']=$data['volumex'];
            $datadt['bobot']=$data['bobotx'];

   
            $qdetIns = updateQuery($dbname,"vhc_progproject_dt",$datadt,"id='".$data['id']."' and induk='".$data['induk']."' and kegiatan='".$data['kegiatan']."'");
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }

            $datadt=array();
            $datadt['volume_berjalan']=$data['volumebjlnnow'];
            $datadt['bobot_berjalan']=$data['bobotbjlnnow'];

            $qdetIns = updateQuery($dbname,"project_dt",$datadt,"kodeproject='".$data['kodeproject']."' and kegiatan='".$data['kegiatan']."'");
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
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
            
            $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$data['kodeproject']."' group by deskripsi order by deskripsi";
            $res=fetchData($query);

            foreach ($res as $key => $val) {
        
                $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id from ".$dbname.".project_dt a 
                left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan  where a.kodeproject='".$val['kodeproject']."' and a.deskripsi='".$val['deskripsi']."' and  b.induk='".$data['kode']."'";
                //exit($query2);
                $res2=fetchData($query2);
                foreach ($res2 as $key2 => $val2) {
                    $volumebj = (floatval($val2['volume_berjalan'])-floatval($val2['volumex']));
                    $bobotbj = (floatval($val2['bobot_berjalan'])-floatval($val2['bobotx']));
                    $datax = array('volume_berjalan'=>$volumebj,'bobot_berjalan'=>$bobotbj);
                    //exit("Error : ".$volumebj);
                    $qdetInsz = updateQuery($dbname,"project_dt",$datax,"kegiatan='".$val2['kegiatan']."' and kodeproject='".$data['kodeproject']."'");
                        try{
                            $owlPDO->exec($qdetInsz); 
                        }
                        catch (PDOException $e) 
                        {
                            print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                        }
                }
            }

            $qdetIns = deleteQuery($dbname,"vhc_progproject","kode='".$data['kode']."'");
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

    case 'excel':
        $tmp=explode(',',$_GET['coloumn']);
        $kode=$tmp[0];
        $kodeorg=$tmp[1];
        $kodeproject=$tmp[2];
        $mingguke=$tmp[3];
        $tglawal=$tmp[4];
        $tglakhir=$tmp[5];
        $optKp=makeOption($dbname,'project','kode,nama');

        //exit("Error : ".$kodeorg);
        $sInduk="select induk,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
        $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
        $qInduk->setFetchMode(PDO::FETCH_ASSOC);
        $rInduk=$qInduk->fetch();

        $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch()){
            $nama=$bar1->namaorganisasi;
        }

        $arrmk1=array();
        if(intval($mingguke)>1){
            $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$kodeproject."' group by deskripsi order by deskripsi";
            $res=fetchData($query);
            for ($xz=1; $xz < intval($mingguke) ; $xz++) {
                foreach ($res as $key => $val) {
                    $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id,c.mingguke from ".$dbname.".project_dt a 
                    left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan 
                    left join ".$dbname.".vhc_progproject c on b.induk=c.kode   where a.kodeproject='".$val['kodeproject']."' and 
                    a.deskripsi='".$val['deskripsi']."' and c.mingguke='".(intval($xz))."'";
                    
                    $res2=fetchData($query2);
                    foreach ($res2 as $key2 => $val2) {
                        $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['volume']+=$val2['volumex'];
                        $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['bobot']+=$val2['bobotx'];
                    }

                }
            }

            for ($xz=1; $xz <= intval($mingguke) ; $xz++) { 
                foreach ($res as $key => $val) {
                    $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id,c.mingguke from ".$dbname.".project_dt a 
                    left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan 
                    left join ".$dbname.".vhc_progproject c on b.induk=c.kode   where a.kodeproject='".$val['kodeproject']."' and 
                    a.deskripsi='".$val['deskripsi']."' and c.mingguke='".(intval($xz))."'";
                    
                    $res2=fetchData($query2);
                        foreach ($res2 as $key2 => $val2) {
                            $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume']+=$val2['volumex'];
                            $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot']+=$val2['bobotx'];
                        }

                }
            }
        
        }


        $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$kodeproject."' group by deskripsi order by deskripsi";
        $res=fetchData($query);

        $tabs="<style>
        @page { margin: 170px 30px; }
        #header { position: fixed; left: 0px; top: -145px; right: 0px; height: 140px;}
        </style>";
        if(intval($mingguke)>1){
        $tabs.="<div id='header'>";
        $tabs.="<table cellspacing=0 border=0;  width=100% align=center>";
        $tabs.="<tr>";
        $tabs.="<td colspan=13 style=font-weight:bold;><font size=3>".$nama."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td colspan=13  style=font-weight:bold;><font size=2>".$rInduk['namaorganisasi']."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td  colspan=13 style='font-weight:bold;text-align:center;'><font size=3>PROGRESS REPORT</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td colspan=13  style='text-align:center;'><font size=1>LAPORAN MINGGUAN</font></td>";
        $tabs.="</tr>";


        $tabs.="<tr>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$optKp[$kodeproject]."</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:right;'><font size=1>Minggu Ke</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$mingguke."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>LOKASI</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$nama."</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:right;'><font size=1>Tanggal</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".tanggalnormal($tglawal)." s/d ".tanggalnormal($tglakhir)."</font></td>";
        $tabs.="</tr>";
        $tabs.="</table></div>";
        }
        else
        {
        $tabs.="<div id='header'>";
        $tabs.="<table cellspacing=0 border=0;  width=100% align=center>";
        $tabs.="<tr>";
        $tabs.="<td colspan=9 style=font-weight:bold;><font size=3>".$nama."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td colspan=9  style=font-weight:bold;><font size=2>".$rInduk['namaorganisasi']."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td  colspan=9 style='font-weight:bold;text-align:center;'><font size=3>PROGRESS REPORT</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td colspan=9  style='text-align:center;'><font size=1>LAPORAN MINGGUAN</font></td>";
        $tabs.="</tr>";


        $tabs.="<tr>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$optKp[$kodeproject]."</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:right;'><font size=1>Minggu Ke</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$mingguke."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>LOKASI</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$nama."</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1></font></td>";
        $tabs.="<td style='text-align:right;'><font size=1>Tanggal</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".tanggalnormal($tglawal)." s/d ".tanggalnormal($tglakhir)."</font></td>";
        $tabs.="</tr>";
        $tabs.="</table></div>";    
        }
        $tabs.="<div id='content'><table cellspacing=0 border=1;  width=100% align=center>";
        $tabs.="<thead>";
        if(intval($mingguke)>1){
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>NO</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>ITEM PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>VOLUME</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>SAT</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>BOBOT</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>S/D Minggu Lalu</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Minggu Ini</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>S/D Minggu Ini</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Sisa Pekerjaan</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";

        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";


        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";


        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";
        $tabs.="</tr>";
        }
        else
        {
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>NO</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>ITEM PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>VOLUME</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>SAT</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>BOBOT</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Minggu Ini</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Sisa Pekerjaan</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";

        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";

        $tabs.="</tr>";
        }
        $tabs.="</thead>";
        $tabs.="<tbody>";
        $total=array();
        if(intval($mingguke)>1){
        foreach ($res as $key => $val) {
            $arrsubtot=array();
            $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=2>".romawi($key+1)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2>".$val['deskripsi']."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";

            $tabs.="</tr>";
            $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id from ".$dbname.".project_dt a 
            left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan  where a.kodeproject='".$val['kodeproject']."' and 
            a.deskripsi='".$val['deskripsi']."' and  b.induk='".$kode."'";
            $res2=fetchData($query2);
            foreach ($res2 as $key2 => $val2) {
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume']+=$val2['volume'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot']+=$val2['bobot'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkvolume']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['volume'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkbobot']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['bobot'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex']+=$val2['volumex'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx']+=$val2['bobotx'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdvolume']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdbobot']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume']+=($val2['volume']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume']);
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot']+=($val2['bobot']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot']);
                $tabs.="<tr>";
                $tabs.="<td style='text-align:center;'><font size=1>".($key2+1)."</font></td>";
                $tabs.="<td style='text-align:left;'><font size=1>".$val2['namakegiatan']."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volume'],2)."</font></td>";
                $tabs.="<td style='text-align:left;'><font size=1>".$val2['satuan']."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobot'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['volume'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['bobot'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volumex'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobotx'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['volume']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume']),2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['bobot']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot']),2)."</font></td>";
                $tabs.="</tr>";
            }
            $total['volume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'];
            $total['bobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'];
            $total['volumex']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'];
            $total['bobotx']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'];
            $total['mkvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkvolume'];
            $total['mkbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkbobot'];
            $total['sdvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdvolume'];
            $total['sdbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdbobot'];
            $total['ssvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'];
            $total['ssbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'];
            $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>Sub Jumlah</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkbobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdbobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'],2)."</font></td>";
            $tabs.="</tr>";
        }
        $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>TOTAL</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['mkvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['mkbobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volumex'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobotx'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['sdvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['sdbobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssbobot'],2)."</font></td>";
            $tabs.="</tr>";
        }
        else
        {
         foreach ($res as $key => $val) {
            $arrsubtot=array();
            $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=2>".romawi($key+1)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2>".$val['deskripsi']."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";

            $tabs.="</tr>";
            $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id from ".$dbname.".project_dt a 
            left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan  where a.kodeproject='".$val['kodeproject']."' and 
            a.deskripsi='".$val['deskripsi']."' and  b.induk='".$kode."'";
            $res2=fetchData($query2);
            foreach ($res2 as $key2 => $val2) {
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume']+=$val2['volume'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot']+=$val2['bobot'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex']+=$val2['volumex'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx']+=$val2['bobotx'];
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume']+=($val2['volume']-$val2['volumex']);
                $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot']+=($val2['bobot']-$val2['bobotx']);
                $tabs.="<tr>";
                $tabs.="<td style='text-align:center;'><font size=1>".($key2+1)."</font></td>";
                $tabs.="<td style='text-align:left;'><font size=1>".$val2['namakegiatan']."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volume'],2)."</font></td>";
                $tabs.="<td style='text-align:left;'><font size=1>".$val2['satuan']."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobot'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volumex'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobotx'],2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['volume']-$val2['volumex']),2)."</font></td>";
                $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['bobot']-$val2['bobotx']),2)."</font></td>";
                $tabs.="</tr>";
            }
            $total['volume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'];
            $total['bobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'];
            $total['volumex']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'];
            $total['bobotx']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'];
            $total['ssvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'];
            $total['ssbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'];
            $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>Sub Jumlah</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'],2)."</font></td>";
            $tabs.="</tr>";
        }
        $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>TOTAL</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volumex'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobotx'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssbobot'],2)."</font></td>";
            $tabs.="</tr>";   
        }
            
        $tabs.="</tbody>";
        $tabs.="</table>";
        if(intval($mingguke)>1){
        $tabs.="<table border=0px cellspacing=0 width=100% align=right>";
         $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=7><font size=1>Diketahui oleh</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=3><font size=1>Diperiksa oleh</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=3><font size=1>Dilaporkan oleh</font></td>";

            $tabs.="</tr>";

        $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=4><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=3><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=3><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=3><font size=1>________________________</font></td>";

            $tabs.="</tr>";

        $tabs.="</table>";
        }
        else
        {
         $tabs.="<table border=0px cellspacing=0 width=100% align=right>";
         $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=5><font size=1>Diketahui oleh</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=2><font size=1>Diperiksa oleh</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=2><font size=1>Dilaporkan oleh</font></td>";

            $tabs.="</tr>";

        $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=3><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=2><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=2><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=2><font size=1>________________________</font></td>";

            $tabs.="</tr>";

        $tabs.="</table>";   
        }
        $tabs.="</div>";

        $tabs.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];    
        $tglSkrg=date("Ymd");
        $nop_="Progress_Report_".$tglSkrg;
        if(strlen($tabs)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    @unlink('tempExcel/'.$file);
                }
                }   
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tabs))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            //closedir($handle);
        }           
        break;
}
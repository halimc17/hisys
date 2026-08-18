<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_panen.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
#=== Init ===
$tipe = 'tipetransaksi';
$tipeVal = 'PNN';
if($_SESSION['empl']['subbagian']=='')
{
    $whereCont = "tipetransaksi='PNN'";
}
else
{
    $whereCont = "tipetransaksi='PNN' and updateby='".$_SESSION['standard']['userid']."'";
}
$whereContArr = array();
$whereContArr[] = array('tipetransaksi','PNN');

#=== Prep Control & Search
$ctl = array();

# Control
$tmpWhere = json_encode($whereContArr);
$jsWhere = str_replace('"',"'",$tmpWhere);
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd('".$tipeVal."')\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList('".$tipeVal."')\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

# Search
// $ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    // makeElement('sNoTrans','label',$_SESSION['lang']['notransaksi']).
    // makeElement('sNoTrans','text','').
    // makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans('".$tipe."','".$tipeVal."')")).
    // "</fieldset>";
#posting
$optpost=array(''=>'','0'=>'Not Posted','1'=>'Posted');
//$optpost=array(''=>'','0'=>'Masuk','1'=>'Keluar');
#divisi
$whereorg="induk='".$_SESSION['empl']['lokasitugas']."' and tipe='AFDELING'";
$optdivisi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereorg,null,true);

# Search
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend><table border=0>".
			"<tr><td>".
    makeElement('sNoTrans','label',$_SESSION['lang']['notransaksi'])."</td><td>".
    makeElement('sNoTrans','text','',array('style'=>'width:105px'))."</td><td>".
	
	 makeElement('sDivisi','label',$_SESSION['lang']['divisi'])."</td><td>".
    makeElement('sDivisi','select','',array('style'=>'width:105px'),$optdivisi)."</td><td>".
	
	 makeElement('sJurnal','label',$_SESSION['lang']['posting'])."</td><td>".
    makeElement('sJurnal','select','',array('style'=>'width:105px'),$optpost)."</td><td hidden>".
	
	 makeElement('sTipetransaksi','label',$_SESSION['lang']['tipetransaksi'])."</td><td hidden>".
    makeElement('sTipetransaksi','text',$tipeVal,array('style'=>'width:105px'))."</td><td></tr><tr><td>".
	
	makeElement('sTanggal','label',$_SESSION['lang']['tanggal'].' Mulai')."</td><td>".
    makeElement('sTanggal','date','',array('style'=>'width:100px'))."</td><td>".
    
    makeElement('sTanggal2','label',$_SESSION['lang']['tanggal'].' Selesai')."</td><td>".
    makeElement('sTanggal2','date','',array('style'=>'width:100px'))."</td></tr><tr><td></td><td>".
	
    makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans('".$tipe."','".$tipeVal."')"))."</td></tr>".
    "</table></fieldset>";	


#=== Table Aktivitas
# Header
$header = array(
    $_SESSION['lang']['nomor'],'No. BKM',$_SESSION['lang']['organisasi'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['nikmandor'],$_SESSION['lang']['nikmandor1'],$_SESSION['lang']['keraniproduksi'],$_SESSION['lang']['keranimuat'],'updateby'
);

# Content
$cols = "notransaksi,nobkm,kodeorg,tanggal,nikmandor,nikmandor1,nikasisten,keranimuat,jurnal,updateby";
$query = selectQuery($dbname,'kebun_aktifitas',$cols,$whereCont.
    " and kodeorg='".$_SESSION['empl']['lokasitugas']."'",
    "tanggal desc, notransaksi desc",false,10,1);
$data = fetchData($query);
$totalRow = getTotalRow($dbname,'kebun_aktifitas',$whereCont);
if(!empty($data)) {
    $whereKarRow = "karyawanid in (";
    $notFirst = false;
    foreach($data as $key=>$row) {
        if($row['jurnal']==1) {
            $data[$key]['switched']=true;
        }
        $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
        unset($data[$key]['jurnal']);
        
        if($notFirst==false) {
	    if($row['nikmandor']!='') {
		$whereKarRow .= $row['nikmandor'];
		$notFirst=true;
	    } 
	    if($row['nikmandor1']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['nikmandor1'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['nikmandor1'];
		}
	    }
	    if($row['nikasisten']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['nikasisten'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['nikasisten'];
		}
	    }
	    if($row['keranimuat']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['keranimuat'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['keranimuat'];
		}
	    }
            if($row['updateby']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['updateby'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['updateby'];
		}
	    }
	} else {
	    if($row['nikmandor']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['nikmandor'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['nikmandor'];
		}
	    }
	    if($row['nikmandor1']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['nikmandor1'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['nikmandor1'];
		}
	    }
	    if($row['nikasisten']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['nikasisten'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['nikasisten'];
		}
	    }
	    if($row['keranimuat']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['keranimuat'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['keranimuat'];
		}
	    }
             if($row['updateby']!='') {
		if($notFirst==false) {
		    $whereKarRow .= $row['updateby'];
		    $notFirst=true;
		} else {
		    $whereKarRow .= ",".$row['updateby'];
		}
	    }
	}
    }
    $whereKarRow .= ")";
} else {
    $whereKarRow = "";
}
$optKarRow = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKarRow,'0',true);

# Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
    $dataShow[$key]['nikmandor'] = $optKarRow[$row['nikmandor']];
    $dataShow[$key]['nikmandor1'] = $optKarRow[$row['nikmandor1']];
    $dataShow[$key]['nikasisten'] = $optKarRow[$row['nikasisten']];
    $dataShow[$key]['keranimuat'] = $optKarRow[$row['keranimuat']];
    $dataShow[$key]['updateby'] = $optKarRow[$row['updateby']];
}

# Posting --> Jabatan
$postJabatan = getPostingJabatan('panen');



# Make Table
$tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
#$tHeader->addAction('showDetail','Detail','images/'.$_SESSION['theme']."/detail.png");
$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
$tHeader->_actions[0]->addAttr($tipeVal);

//$tHeader->addAction('','','images/'.$_SESSION['theme']."/delete.png");
if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
	$tHeader->addAction('','','images/'.$_SESSION['theme']."/delete.png");
}else{
	$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
}
//$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
#$tHeader->addAction('approveData','Approve','images/'.$_SESSION['theme']."/approve.png");
$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
    $tHeader->_actions[2]->_name='';
}
$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
$tHeader->_actions[3]->addAttr('event');
$tHeader->_actions[3]->addAttr($tipeVal);
$tHeader->addAction('detailData','Print Data Detail','images/'.$_SESSION['theme']."/zoom.png");
$tHeader->_actions[4]->addAttr('event');
$tHeader->_actions[4]->addAttr($tipeVal);

$tHeader->addAction('detailExcel','Print Data Detail','images/excel.jpg');
$tHeader->_actions[5]->addAttr('event');
$tHeader->_actions[5]->addAttr($tipeVal);

$tHeader->pageSetting(1,$totalRow,10);
$tHeader->setWhere($whereContArr);
//$tHeader->_switchException = array();
$tHeader->_switchException = array('detailData','detailPDF','detailExcel');

//$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
//$tHeader->_actions[3]->addAttr('event');
//$tHeader->pageSetting(1,$totalRow,10);
//$tHeader->setWhere($whereContArr);
//$tHeader->_switchException = array('detailPDF');
#echo "<pre>";
#print_r($tHeader);
#=== Display View
# Title & Control
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['panen']).'</span>');
echo "<div><table><tr>";
foreach($ctl as $el) {
    echo "<td v-align='middle' style='min-width:100px'>".$el."</td>";
}
echo "</tr></table></div>";
CLOSE_BOX();

# List
OPEN_BOX();
echo "<div id='workField'>";
$tHeader->renderTable();
echo "</div>";
CLOSE_BOX();
echo close_body();
?>
<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

# Get POST
$IDs = $_POST;
$namaKeg = $_POST['namakegiatan'];
$uom = $_POST['satuan'];
unset($IDs['namakegiatan']);
unset($IDs['satuan']);

# Get Header
$tmpField = getFieldName('setup_kegiatannorma','array');
$tmpPrim = getPrimary($dbname,'setup_kegiatannorma');

$fieldNew = $field = array();
$fieldStr = "";
foreach($tmpField as $row) {
    if($row!='kodeorg' and $row!='kodekegiatan' and $row!='kelompok' and $row!='tipeanggaran' and $row!='topografi' and $row!='kuantitas1' and $row!='kuantitas2' and $row!='pusingan' and $row!='rotasi') {
        $fieldNew[] = $field[] = $row;
        $fieldStr .= "##".$row;
        if($row=='kodebarang') {
            $fieldNew[] = 'namabarang';
            $fieldStr .= "##namabarang";
        }
    }
}

$primaryStr = "";
foreach($tmpPrim as $row) {
    $primaryStr .= "##".$row;
}

#======== Get Data
# Prep Condition
$i=0;
foreach($IDs as $key=>$row) {
    if($i==0) {
        $where = $key."='".$row."'";
    } else {
        $where .= " AND ".$key."='".$row."'";
    }
    $i++;
}

# Fetch Data
$query = selectQuery($dbname,'setup_kegiatannorma',$field,$where);
$tmpData = fetchData($query);
$data = array();
$listInv = array();

# Insert namabarang column
foreach($tmpData as $key=>$row) {
    foreach($row as $head=>$cont) {
        $data[$key][$head] = $cont;
        if($head=='kodebarang') {
            $data[$key]['namabarang'] = '';
            $listInv[] = $cont;
        }
    }
}

# Create Header
$header = array();
foreach($fieldNew as $row) {
  $header[] = $_SESSION['lang'][$row];
}
$header[] = "Add";

#=========== Reform Content from Data =================
$primary = "<table>";
$primary .= "<tr style=display:none><td>".makeElement('kodeorg_norma','label',$_SESSION['lang']['kodeorg'])."</td><td>: ".
    makeElement('kodeorg_norma','text',$IDs['kodeorg'],array('disabled'=>'disabled','style'=>'width:215px'))."</td></tr><tr><td>";
$primary .= makeElement('kodekegiatan_norma','label',$_SESSION['lang']['kodekegiatan'])."</td><td>: ".
    makeElement('kodekegiatan_norma','text',$IDs['kodekegiatan'],array('disabled'=>'disabled','style'=>'width:60px'))."&nbsp;".
    makeElement('namakegiatan_norma','text',$namaKeg,array('disabled'=>'disabled','style'=>'width:190px')).
    "</td></tr><tr  style=display:none><td>";
$primary .= makeElement('kelompok_norma','label',$_SESSION['lang']['kelompok'])."</td><td>: ".
    makeElement('kelompok_norma','text',$IDs['kelompok'],array('disabled'=>'disabled','style'=>'width:215px'))."</td></tr>";
$primary .= "</table>";

$content = array();

# Setting drop down options
$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
$optTipeAng = getEnum($dbname,'setup_kegiatannorma','tipeanggaran');

# Get Nama Barang
$whereNB = "";
foreach($listInv as $key=>$row) {
    if($key==0) {
        $whereNB .= "kodebarang=".$row;
    } else {
        $whereNB .= " or kodebarang=".$row;
    }
}
if($whereNB!="") {
    $query = selectQuery($dbname,'log_5masterbarang','kodebarang,namabarang,satuan',$whereNB);
    $resBar = fetchData($query);
    $namaBarang = array();
    $satuanBarang = array();
    foreach($resBar as $row) {
        $namaBarang[$row['kodebarang']] = $row['namabarang'];
        $satuanBarang[$row['kodebarang']] = $row['satuan'];
    }
}

# Masking Nama Barang
foreach($data as $key=>$row) {
    $data[$key]['namabarang'] = $namaBarang[$row['kodebarang']];
}

# Editable Row
$j=0;
if($data!=array()) {
  foreach($data as $i=>$row) {
    foreach($row as $key=>$data) {
        if($key=='topografi') {
        $content[$i][$key] = makeElement($key."_".$i,'select',$data,
                array('style'=>'width:100px','disabled'=>'disabled'),array($data=>$optTopografi[$data]));
        } elseif($key=='tipeanggaran') {
            $content[$i][$key] = makeElement($key."_".$i,'select',$data,
                array('style'=>'width:200px','disabled'=>'disabled'),array($data=>$optTipeAng[$data]));
        } elseif($key=='kodebarang') {
            $content[$i][$key] = makeElement($key."_".$i,'text',$data,
                array('style'=>'width:70px','readonly'=>'readonly','disabled'=>'disabled')).
                makeElement('getInvBtn_'.$i,'btn','Cari',
                array('onclick'=>'getInv(event,\''.$i.'\')','disabled'=>'disabled'));
        } elseif($key=='namabarang') {
            $content[$i][$key] = makeElement($key."_".$i,'txt',$data,
                array('style'=>'width:200px','disabled'=>'disabled'));
        } elseif($key=='kuantitas1') {
            $content[$i][$key] = makeElement($key."_".$i,'textnum',$data,
                array('style'=>'width:40px','onkeypress'=>'return tanpa_kutip(event)')).
                "&nbsp;<span id='uom1_".$i."'>".$satuanBarang[$row['kodebarang']]."</span>";
        } elseif($key=='kuantitas2') {
            $content[$i][$key] = makeElement($key."_".$i,'textnum',$data,
                array('style'=>'width:40px','onkeypress'=>'return tanpa_kutip(event)')).
                "&nbsp;<span id='uom2_".$i."'>".$uom."</span>";
        } else {
            $content[$i][$key] = makeElement($key."_".$i,'textnum',$data,
                array('style'=>'width:40px','onkeypress'=>'return tanpa_kutip(event)'));
        }
    }
    //$content[$i]['Z'] = "<img id='editNorma_".$i."' title='Edit' class=zImgBtn onclick=\"editNorma('".$i."','".$primaryStr."','".$fieldStr."')\" src='images/".$_SESSION['theme']."/save.png'/>";
    @$content[$i]['Z'] .= "&nbsp;<img id='deleteNorma_".$i."' title='Hapus' class=zImgBtn onclick=\"deleteNorma('".$i."','".$primaryStr."','".$fieldStr."')\" src='images/".$_SESSION['theme']."/delete.png'/>";
    $j = $i+1;
  }
}

# New Row
foreach($fieldNew as $row) {
    if($row=='topografi') {
        $content[$j][$row] = makeElement($row."_".$j,'select','',
            array('style'=>'width:100px'),$optTopografi);
    } elseif($row=='tipeanggaran') {
        $content[$j][$row] = makeElement($row."_".$j,'select','',
            array('style'=>'width:100px'),$optTipeAng);
    } elseif($row=='kodebarang') {
        $content[$j][$row] = makeElement($row."_".$j,'text','',
            array('style'=>'width:70px','readonly'=>'readonly')).
            makeElement('getInvBtn_'.$j,'btn','Cari',array('onclick'=>'getInv(event,\''.$j.'\')'));
    } elseif($row=='namabarang') {
        $content[$j][$row] = makeElement($row."_".$j,'txt','',
            array('style'=>'width:200px','readonly'=>'readonly'));
    } elseif($row=='kuantitas1') {
        $content[$j][$row] = makeElement($row."_".$j,'textnum','0',
            array('style'=>'width:40px','onkeypress'=>'return angka_doang(event)'))."&nbsp;<span id='uom1_".$j."'></span>";
    } elseif($row=='kuantitas2') {
        $content[$j][$row] = makeElement($row."_".$j,'textnum','0',
            array('style'=>'width:40px','onkeypress'=>'return angka_doang(event)'))."&nbsp;<span id='uom2_".$j."'>".$uom."</span>";
    } else {
        $content[$j][$row] = makeElement($row."_".$j,'textnum','0',
            array('style'=>'width:40px','onkeypress'=>'return angka_doang(event)'));
    }
}
$content[$j]['Z'] = "<img id='addNorma_".$j."' title='Tambah' class=zImgBtn onclick=\"addNorma('".$j."','".$primaryStr."','".$fieldStr."')\" src='images/plus.png'/>";
$content[$j]['Z'] .= "&nbsp;<img id='deleteNorma_".$j."' />";

#============= Generate Main Table =======================
$mainTable = makeTable('normaTable','normaBody',$header,$content,array(),true,'detail_tr');
echo "<div id='mainTable'>";
echo "<label><b>Material Kegiatan</b></label><hr>";
echo "<div class='table-scroll' style=max-height:40vh;>";
echo $primary;
echo $mainTable;
echo "</div></fieldset></div>";



/***********************************************************************************************************/
/***********************************************************************************************************/
/***********************************************************************************************************/



$optunit = "<option value=''></option>";
$str="select * from ".$dbname.".organisasi where tipe='KEBUN' order by induk, kodeorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
	
}


echo"<div style=clear:both></div>";
// echo"<fieldset><legend>Premi Kegiatan</legend>";
echo"<br><label><b>Premi Kegiatan</b></label><hr>";
echo"<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select class=select2 id=unitpr style=\"width:150px;\">".$optunit."</select></td>
				
					<td>".$_SESSION['lang']['jumlahbasis']."</td> 
					<td>:</td>
					<td><input type=text id=basispr onkeypress=\"return angka_doang(event);\"  class=myinputtextnumber style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premlebihbasis']."</td> 
					<td>:</td>
					<td><input type=text id=premilbpr onkeypress=\"return angka_doang(event);\"  class=myinputtextnumber style=\"width:145px;\"></td>
				
					<td></td>
					<td></td>
					<td colspan=3>
						<button class=mybutton onclick=simpanpr()>Simpan</button>
					</td>
				</tr>
			</table>
			<div style=clear:both></div><hr>
			";
			
		
			
			echo "
					<div id=containerpr class='table-scroll' style=max-height:40vh;> 
						<script>loadpr()</script>
					</div>
				
			
		
			</fieldset>";
			
			
			/*
				<fieldset style=float:left>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table border=0 cellpadding=1 cellspacing=1>
				<thead>
					<tr class=rowheader>
					<td>".$_SESSION['lang']['unit']."</td>
					
					<td>Basis</td>
					<td>Premi Lebih Basis</td>
					<td>".$_SESSION['lang']['action']."</td></tr></thead>";
			
			
			$str="select * from ".$dbname.".kebun_5premibkm where kodekegiatan='".$IDs['kodekegiatan']."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				echo"
					<tr class=rowcontent>
					<td>".$bar['unit']."</td>
					<td align=right>".$bar['basis']."</td>
					<td align=right>".$bar['premilebihbasis']."</td>
					<td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delpr('".$bar['unit']."','".$IDs['kodekegiatan']."');\" ></td></tr>
				";
			}
			*/
			
			
			
			
			
			
			
?>
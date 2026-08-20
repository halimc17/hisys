<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/log_realisasispkx.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('log_realisasispkx').'</span><br>');

##Get Unit & Sub unit
$where="";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
// 	$where.= " and length(kodeorg)=4";
// }else if($_SESSION['empl']['tipelokasitugas']=='TRAKSI' or $_SESSION['empl']['tipelokasitugas']=='KANWIL') {
// 	$where.= " and length(kodeorg)=4 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$kdOrganisasi."') ";
// } else {
// 	$where.= " and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
// }

$str="select distinct(kodeorg) as kodeorg from ".$dbname.".log_spkht where 1=1 and kodeorg in (".getOrgDetail('2').") order by kodeorg asc";
$res=fetchdata($str);
$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($res as $val){
	$optunit.="<option value='".$val['kodeorg']."'>".$val['kodeorg']." - ".getNamaOrg($val['kodeorg'])."</option>";
}

echo"<div id=action_list>
	<table>
		<tr valign=middle>
			<td align=center style='width:100px;cursor:pointer;' onclick=getpage()>
				<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
			</td>
			
			<td>
			<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
			<table>
				<tr>
					<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
					<td>:</td>
					<td><input type='text' id=notransaksicr class='myinputtext' style=\"width:150px;\"></td>
					
					<td style='padding-left:20px'>" . $_SESSION['lang']['unit'] . "</td> 
					<td>:</td>
					<td><select id='unitcr'>".$optunit."</select></td>
					
					<td style='padding-left:20px'>" . $_SESSION['lang']['koderekanan'] . "</td> 
					<td>:</td>
					<td><input type='text' id=koderekanancr class='myinputtext' style=\"width:150px;\"></td>
					
					<td style='padding-left:20px'>" . $_SESSION['lang']['tanggal'] . "</td> 
					<td>:</td>
					<td>
						<input id='tglcr' class='myinputtext' type='text' onmousemove='setCalendar(this.id)' style='width:80px' readonly>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td style='text-align:left'>
						<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
					</td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table> ";
CLOSE_BOX();
echo "</div>"; 


OPEN_BOX();
echo"<div id=listData style=display:block>";
echo "
	<div>  
		<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
			<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>" . $_SESSION['lang']['unit'] . "</td>
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
				<td align=center>" . $_SESSION['lang']['subunit'] . "</td>
				<td align=center>" . $_SESSION['lang']['koderekanan'] . "</td>
				<td align=center>" . $_SESSION['lang']['nilaikontrak'] . "</td>
				<td align=center>" . $_SESSION['lang']['matauang'] . "</td>
				<td align=center>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>
				<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
				<td align=center colspan=3>" . $_SESSION['lang']['action'] . "</td>
			</thead>
			<tbody id=container>
				<script>loaddata(0)</script>
			</tbody>
		</table>
	</div>
</div>
<div id='workField' style='display:none'></div>";
CLOSE_BOX();

echo close_body();


// #=== Prep Control & Search
// $ctl = array();

// # Control
// #$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
// #    $_SESSION['lang']['new']."' onclick=\"showAdd()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
// $ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    // $_SESSION['lang']['list']."' onclick=\"defaultList()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

// # Search
// $ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    // makeElement('sNoTrans','label',$_SESSION['lang']['notransaksi']).
    // makeElement('sNoTrans','text','').
    // makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans()")).
    // "</fieldset>";


// #=== Table Aktivitas
// # Header
// $header = array(
    // $_SESSION['lang']['kebun'],
    // $_SESSION['lang']['notransaksi'],
    // $_SESSION['lang']['tanggal'],
    // $_SESSION['lang']['subunit'],
    // $_SESSION['lang']['koderekanan'],
    // $_SESSION['lang']['nilaikontrak'],
	// $_SESSION['lang']['matauang'],
    // $_SESSION['lang']['jumlahrealisasi'],
    // $_SESSION['lang']['status']    
// );


// # Content
// $kdOrganisasi = $_SESSION['empl']['kodeorganisasi'];
// $cols = "kodeorg,notransaksi,tanggal,divisi,koderekanan,nilaikontrak,matauang";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	// $where = "length(kodeorg)=4";
// }else if($_SESSION['empl']['tipelokasitugas']=='TRAKSI' or
   // $_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    // $where = "length(kodeorg)=4 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$kdOrganisasi."')";
// } else {
    // $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
// }
// $query = selectQuery($dbname,'log_spkht',$cols,$where ." order by tanggal desc","",false,25,1);
// $data = fetchData($query);
// $totalRow = getTotalRow($dbname,'log_spkht');
// foreach($data as $key=>$row) {
    // $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
    // $data[$key]['nilaikontrak'] = number_format($row['nilaikontrak']);
// //=================ambil realisasi
            // $data[$key]['realisasi'] =0;
            // $strx="select sum(jumlahrealisasi) from ".$dbname.".log_baspk 
                  // where notransaksi='".$data[$key]['notransaksi']."' and statusjurnal = '1'";
			// $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			// $resx->setFetchMode(PDO::FETCH_NUM);
            // while($barx=$resx->fetch())
            // {
              // $data[$key]['realisasi']= number_format($barx[0]); 
            // }   
			
			// //lihat postingan-=============================
            // $data[$key]['status'] ='';
            // $strx="select statusjurnal from ".$dbname.".log_baspk 
                  // where notransaksi='".$data[$key]['notransaksi']."' and statusjurnal=0";
			// $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			// if(owlBaris($resx)>0)
                // $data[$key]['status'] ='?';
            // else if($data[$key]['realisasi']==0 and $data[$key]['status']=='')
                // $data[$key]['status'] ='?';
            // else                
               // $data[$key]['status'] ='Posted';    
// }

// # Options
// if(!empty($data)) {
    // $whereSupp = "supplierid in (";
    // foreach($data as $key=>$row) {
      // if($key==0) {
        // $whereSupp .= "'".$row['koderekanan']."'";
      // } else {
        // $whereSupp .= ",'".$row['koderekanan']."'";
      // }
    // }
    // $whereSupp .= ")";
// } else {
    // $whereSupp = null;
// }
// $optSupp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
    // $whereSupp);

// # Data Show
// $dataShow = $data;
// foreach($dataShow as $key=>$row) {
	// $dataShow[$key]['koderekanan'] = isset($optSupp[$row['koderekanan']])? $optSupp[$row['koderekanan']]: '';
	// if($dataShow[$key]['divisi'] == ''){
		// $dataShow[$key]['divisi'] = "Project";
	// }else if($dataShow[$key]['divisi'] == 'S'){
		// $dataShow[$key]['divisi'] = "Perumahan";
	// }else{
		// $dataShow[$key]['divisi'] = $dataShow[$key]['divisi'];
	// }
// }

// # Make Table
// $tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
// $tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
// #$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
// #$tHeader->_actions[1]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
// $tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
// $tHeader->addAction('viewdetail','View','images/'.$_SESSION['theme']."/zoom.png");
// #$tHeader->addAction('UploadFile','Upload','images/upload-2-xxl.png');
// $tHeader->_actions[1]->addAttr('event');
// $tHeader->_switchException = array('detailPDF');
// $tHeader->pageSetting(1,$totalRow,25);
// #echo "<pre>";
// #print_r($tHeader);
// #=== Display View
// # Title & Control
// OPEN_BOX('','<span class=judul>'.getMenu('log_realisasispk').'</span>');
// // list kumpulan KP by dz. dipake buat ngebandingan apakah ada kegiatan kontrak plus
        // $str="select nilai from ".$dbname.".setup_parameterappl
            // where kodeaplikasi = 'KP'";  
        // $hasil='';
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_OBJ);
        // while($bar=$res->fetch())
        // {
            // if($hasil!='')$hasil.='####';
            // $hasil.=$bar->nilai;
        // }
        // echo "<input type=hidden id=listkp name=listkp value='".$hasil."'>";


// echo "<div><table><tr>";
// foreach($ctl as $el) {
    // echo "<td v-align='middle' style='min-width:100px'>".$el."</td>";
// }
// echo "</tr></table></div>";
// CLOSE_BOX();

// # List
// OPEN_BOX();
// echo "<div id='workField'>";
// $tHeader->renderTable();
// echo "</div>";
// CLOSE_BOX();
// echo close_body();
?>
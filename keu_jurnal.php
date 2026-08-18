<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script language=javascript src='js/keu_jurnal.js?v=<?php echo time(); ?>'></script>
<script languange=javascript1.2>
    //zGrid.column.push(1);
    theGrid[1].addColumn('nourut','<?php echo $_SESSION['lang']['nourut']?>','textnum',0,'R',10);
    theGrid[1].addColumn('noakun','<?php echo $_SESSION['lang']['noakun']?>','text','-','L',14);
    theGrid[1].addColumn('keterangan','<?php echo $_SESSION['lang']['keterangan']?>','text','-','L',50);
    theGrid[1].addPrimColumn('nojurnal','nojurnal');
    theGrid[1].target = "keu_slave_jurnal_manage_detail";
</script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['jurnal']).'</span>');

$_SESSION['imgjurnalm'] = array();

#== Prep Periode Akuntansi
$org = $_SESSION['org'];
$period = $_SESSION['org']['period'];
if($period=='') {
    echo "Error : There ain`t active accounting period";
    CLOSE_BOX();
    echo close_body();
    exit;
}

if($_SESSION['empl']['tipelokasitugas']=='KEBUN' || $_SESSION['empl']['tipelokasitugas']=='PABRIK'){
	$listunit ="'".$_SESSION['empl']['lokasitugas']."'";
	$jmlist="substr(nojurnal,10,4) = ".$listunit." ";
}else{
	//$where = "kodeorg in (".getOrgDetail(2).")";
	$listunit = getOrgDetail(2);
	$jmlist="substr(nojurnal,10,4) in (".$listunit.")";
}


$arrlistunit=explode(',',str_replace("'","",$listunit));
// echo"<pre>";
// print_r($arrlistunit);
// exit();
foreach($arrlistunit as $key){
	// $arrker[]=$key;
	
	$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$key."' and tutupbuku=0 order by periode asc limit 1";
	// echo $str;exit();
	$res=fetchdata($str);
	foreach($res as $bar){
		$dttglmulai[$bar['kodeorg']]=$bar['tanggalmulai'];
	}
}



$str="select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg in (".$listunit.") and tutupbuku=0 order by periode asc limit 1";

$res=fetchdata($str);
foreach($res as $bar){
	$tanggalmulai=$bar['tanggalmulai'];
}



#== Get Journal Header
// $where = " tanggal>=".$period['start'].
//     " and ".$jmlist." and kodejurnal='M'".
//     " and revisi=0 and noreferensi not like '%/BK/%' and noreferensi not like '%-GI-%' and noreferensi not like '%-GR-%' and noreferensi not like '%/TBM/%' and noreferensi not like '%/TM/%'";
$where = " tanggal>=".$tanggalmulai.
    " and ".$jmlist." and kodejurnal='M'".
    " and revisi=0 and autojurnal = '0' ";
/*
$where = " tanggal>=".$period['start'].
    " and ".$jmlist." and kodejurnal='M'".
    " and revisi=0 and autojurnal = '0' ";
*/	
	
$query = selectQuery($dbname,'keu_jurnalht',"kodejurnal,nojurnal,tanggal,noreferensi,matauang,totaldebet,totalkredit",$where,"nojurnal desc");
// echo $query;
/*$query="select kodejurnal,nojurnal,tanggal,noreferensi,matauang, sum(debet) as totaldebet, sum(kredit) as totalkredit 
from ".$dbname.".keu_jurnaldt_vw where ".$where." and nodok not like '%HTGTBS%' and nodok not like '%PO%' 
and nodok not like '%SO-HO%' group by nojurnal order by nojurnal desc";*/
// exit('warning : '.$query);
$resTab = fetchData($query);

#== Prep List Header
$header = array($_SESSION['lang']['kodeabs'],$_SESSION['lang']['nojurnal'],
$_SESSION['lang']['tanggal'],$_SESSION['lang']['nodok'],$_SESSION['lang']['matauang'],$_SESSION['lang']['debet'],
$_SESSION['lang']['kredit'],$_SESSION['lang']['keterangan'],$_SESSION['lang']['file']);



$dtlistunit=str_replace("'","",$listunit);
$dtlistunit=explode(",",$dtlistunit);
$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($dtlistunit as $data){
	$optunit.="<option value='".$data."'>".$data."</option>";
}

$table = "<table>";
$table .= "<tr>";
$table .=  "<td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select id=unitcr style='width:200px;' onchange=loadHeader()>".$optunit."</select></td><td></td>";
$table .= "</tr>";
$table .= "</table>";


$table .= "<table id='listHeader' class='sortable'>";
$table .= "<thead><tr class='rowheader'>";
$table .= "<td colspan='2'>".$_SESSION['lang']['action']."</td>";
if(isset($header)){
	foreach($header as $head) {
		$table .= "<td>".$head."</td>";
	}
}
// $table .= "<td >".$_SESSION['lang']['status']."</td>";
$table .= "</tr></thead>";
$table .= "<tbody id='bodyListHeader'>";
if(isset($resTab)){
	foreach(@$resTab as $key=>$row) {
		
		#= cek masing2 periode akuntansi
		
		// $tablex = "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
		// $tablex .= "<td id='pdf_".$key."'><img src='images/".$_SESSION['theme']."/pdf.jpg' ";
		// $tablex .= "class='zImgBtn' onclick='detailPDF(".$key.",event)'></td>";
		// $tablex .= "<td id='delHead_".$key."'>";
		// $tablex .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
		// $tablex .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
        $notablex = 1;
		
		if(isset($row)){
			foreach(@$row as $col=>$dat) {
				if($col=='tanggal') {
					$dat = tanggalnormal($dat);
				}
				
				// $query = selectQuery($dbname,'keu_jurnalht','autojurnal',"nojurnal='".$row['nojurnal']."'");
				// $res = fetchData($query);
				// $bar =$res[0];
				// $autojurnal=$bar['autojurnal'];
				// if ($autojurnal==1){
						// continue;
				// }
				
				
			
				
				if($dttglmulai[substr($row['nojurnal'],9,4)]>$row['tanggal']){
					continue;
				}
			

				if($notablex==1)
                {
                    $notablex=2;
                    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
                    $table .= "<td id='pdf_".$key."'><img src='images/".$_SESSION['theme']."/pdf.jpg' ";
                    $table .= "class='zImgBtn' onclick='detailPDF(".$key.",event)'></td>";
                    $table .= "<td id='delHead_".$key."'>";
                    $table .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
                    $table .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
                }

				$dtplus=0;
				$dtmin=0;
				$krngan=0;
				$sData=$owlPDO->query("select distinct sum(jumlah) as plus,kodeorg from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah>0");
				$sData->setFetchMode(PDO::FETCH_ASSOC);
				$rData= $sData->fetch();
				$dtplus=$rData['plus'];
				
				
				$sData=$owlPDO->query("select distinct sum(jumlah) as min,kodeorg, keterangan as keterangan from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah<0");
				//exit("Error:".$sData);
				$sData->setFetchMode(PDO::FETCH_ASSOC);
				$rData= $sData->fetch();
				$dtmin=$rData['min']*(-1);
				$kodeunit=$rData['plus'];
				  
				$stat=array('0'=>$_SESSION['lang']['disetujui'],'2'=>$_SESSION['lang']['koreksi'],'3'=>$_SESSION['lang']['ditolak'],'9'=>$_SESSION['lang']['diajukan'] );

				$ttl="";
				$strap="select * from ".$dbname.".approval where notransaksi='".$row['nojurnal']."' order by level asc";    
				$resap=$owlPDO->query($strap) or die(print "Gagal : ".PDOException::getMessage());
				$resap->setFetchMode(PDO::FETCH_ASSOC);
				while($barap=$resap->fetch())
				{
					$ttl.="Persetujuan ".$barap['level']." : ".$barap['komentar']."\n";
				}

				$klik="passEditHeader(".$key.")";

				$sCekData=$owlPDO->query("select sum(jumlah) as selisih from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."'");
				$sCekData->setFetchMode(PDO::FETCH_ASSOC);
				$rCekData=$sCekData->fetch();
			  
				$dbgr="";
				if(intval($rCekData['selisih'])!=0)
				{
				 $dbgr="bgcolor='red'";
				}
				// if(number_format($dtplus,0) == 0 && number_format($dtmin,0) == 0)
				// {
				 // $dbgr="bgcolor='red'";
				// }
				if($col=='totaldebet')
				{
					$table .= "<td id='".$col."_".$key."' onclick='".$klik."' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtplus,0)."</td>";
				}
				elseif($col=='totalkredit')
				{
					$table .= "<td id='".$col."_".$key."' onclick='".$klik."' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtmin,0)."</td>";
				}
				else
				{
					$table .= "<td id='".$col."_".$key."' onclick='".$klik."' ".$dbgr.">".$dat."</td>";
				}
				
			}
		}
		if($notablex==2)
        {
        	$notablex=0;
			$table .= "<td id='".$col."_".$key."' onclick='".$klik."' ".$dbgr.">".$rData['keterangan']."</td>";
			
			## SHOW IMAGE UPLOAD
			$nox=0;
			$showimg="";
			$strx="select * from ".$dbname.".listfileupload where notransaksi='".$row['nojurnal']."'";
			$resx=fetchData($strx);
			foreach($resx as $valx){
				$nox++;
				if($nox==1){
					$showimg.=$nox.". <a href='fileupload/jm/".$valx['namafile']."' title='Klik disini untuk download file' target='_blank'>".$valx['namafile']."</a>";					
				}else{
					$showimg.="<br>".$nox.". <a href='fileupload/jm/".$valx['namafile']."' title='Klik disini untuk download file' target='_blank'>".$valx['namafile']."</a>";					
				}
			}
			$table .= "<td>".$showimg."</td>";
			$table .= "</tr>";
        }
		// $table .= "<td id='".$col."_".$key."' onclick='".$klik."' ".$dbgr." title='".$ttl."'>".$stat[$autojurnal]."</td>";
	}
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

#== Prep Form Header
# Options
// $optCurr = makeOption($dbname,'setup_matauang','kode,matauang',"kode='IDR'");
// $strCurr="select kode,matauang from ".$dbname.".setup_matauang order by FIELD(kode,'IDR') DESC, matauang";
$strCurr="select kode,matauang from ".$dbname.".setup_matauang where kode='IDR'";
$resCurr = fetchData($strCurr);
if(isset($resCurr)){
	foreach(@$resCurr as $key=>$val)
	{
		$optCurr[$val['kode']] = $val['matauang']; 
	}
}

$optJCode = makeOption($dbname,'keu_5kelompokjurnal','kodekelompok,keterangan',
    "kodeorg='".$org['kodeorganisasi']."' and kodekelompok='M'");
	
## Organisasi Detail ##
$optKUnit = getOrgDetail(9);

$kodeorg=getOrgDetail(2);
##persetujuan1
$str="select a.namakaryawan,a.nik,b.karyawanid,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit in (".$kodeorg.") and b.jenispersetujuan='JM' and b.level='1' order by a.namakaryawan";
$res=fetchData($str);
if(isset($res)){
	foreach (@$res as $row=>$lst) {
		$arrper1[$lst['karyawanid']]=$lst['namakaryawan']." - ".$lst['lokasitugas'];
	}
}

##persetujuan2
$str="select a.namakaryawan,a.nik,b.karyawanid,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit in (".$kodeorg.") and b.jenispersetujuan='JM' and b.level='2' order by a.namakaryawan";
$res=fetchData($str);
if(isset($res)){
	foreach (@$res as $row=>$lst) {
		$arrper2[$lst['karyawanid']]=$lst['namakaryawan']." - ".$lst['lokasitugas'];
	}
}

# Elements
$els = array();
$els[] = array(
    makeElement('nojurnal','label',$_SESSION['lang']['nojurnal']),
    makeElement('nojurnal','text','',array('style'=>'width:145px',
        'readonly'=>'readonly','disabled'=>'disabled'))." <i>*) Journal Number Automatic</i>"
);
$els[] = array(
    makeElement('kodejurnal','label',$_SESSION['lang']['kodejurnal']),
    makeElement('kodejurnal','select','',array('style'=>'width:150px',
        'disabled'=>'disabled'),$optJCode)
);
$els[] = array(
    makeElement('kodeunit','label',$_SESSION['lang']['unit']),
    makeElement('kodeunit','select','',array('style'=>'',
        'disabled'=>'disabled'),$optKUnit)
);
$els[] = array(
    makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
    makeElement('tanggal','date','',array('style'=>'width:80px','readonly'=>'readonly',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
$els[] = array(
    makeElement('noreferensi','label',$_SESSION['lang']['noreferensi']),
    makeElement('noreferensi','text','',array('style'=>'width:145px','maxlength'=>'25',
        'onkeypress'=>'return tanpa_kutip(event)','disabled'=>'disabled'))
);
$els[] = array(
    makeElement('matauang','label',$_SESSION['lang']['matauang']),
    makeElement('matauang','select','',array('style'=>'width:85px',
        'disabled'=>'disabled'),$optCurr)
);
$els[] = array(
    makeElement('fileupload','label',$_SESSION['lang']['file']),
	"<table class='sortable' cellspacing=1>
		<thead>
		<tr class='rowheader'>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['namafile']."</td>
			<td>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='containerupload' style='text-align:center'>
			<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['datanotfound']."</td>
			</tr>
		</tbody>
		<tbody>
		<tr class='rowcontent' style='text-align:center'>
			<td colspan=2>
				<input type='file' name='upload' id='upload' class=mybutton disabled>
			</td>
			<td>
				<img src=images/plus.png class=resicon id='addfile' title='Add File'>
			</td>
		</tr>
		</tbody>
	</table>"
);
// $els[] = array(
    // makeElement('persetujuan1','label',$_SESSION['lang']['persetujuan'].'1'),
    // makeElement('persetujuan1','select','',array('style'=>'width:145px',
        // 'disabled'=>'disabled'),$arrper1)
// );
// $els[] = array(
    // makeElement('persetujuan2','label',$_SESSION['lang']['persetujuan'].'2'),
    // makeElement('persetujuan2','select','',array('style'=>'width:145px',
        // 'disabled'=>'disabled'),$arrper2)
// );

$els['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],
        array('disabled'=>'disabled'))
);
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
#===== Show =======


# Active Form
echo makeElement('startPeriod','hidden',$_SESSION['org']['period']['start']);
echo makeElement('endPeriod','hidden',$_SESSION['org']['period']['end']);
echo "<fieldset id='fieldFormHeader' style='clear:right;min-height:auto;'>";
echo "<legend><b>New Header</b></span></legend>";
echo "<img id='addHeadBtn' src='images/".$_SESSION['theme']."/plus.png' style='cursor:pointer;height:17px;' title='Create new transaction' onclick=\"addModeForm('".$_SESSION['theme']."')\" />";
echo genElement($els);
echo "</fieldset>";
# Detail List
echo "<fieldset id='fieldListDetail' style='clear:both;'>";
echo "<legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</b></legend>";
echo "<div id='divDetail'></div>";
echo "</fieldset>";

# Table
echo "<fieldset id='fieldListTable' clear:left;min-height:200px;height:100%;overflow:auto'>";
echo "<legend><b>Header List</b></legend>";
//echo "<img id='addHeadBtn' src='images/".$_SESSION['theme']."/plus.png' style='cursor:pointer' onclick=\"addModeForm()\" />".
//    "<a style='cursor:pointer' onclick=\"addModeForm('".$_SESSION['theme']."')\">Tambah Header</a>";
echo"<div style='height:350px;width:auto;overflow:auto;'>";
echo $table;
echo "</div>";
echo "</fieldset>";

CLOSE_BOX();
echo close_body();
?>
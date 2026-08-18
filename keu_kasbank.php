<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>





<script language=javascript1.2>
notifdatecurrency="<?php echo $_SESSION['lang']['notifdatecurrency']; ?>";
notifcurrency="<?php echo $_SESSION['lang']['notifcurrency']; ?>";
notifnoakun="<?php echo $_SESSION['lang']['notifnoakun']; ?>";
notifdaftaruangmuka="<?php echo $_SESSION['lang']['notifdaftaruangmuka']; ?>";
notifnoinvoicepilih="<?php echo $_SESSION['lang']['notifnoinvoicepilih']; ?>";
notifinputcurrency="<?php echo $_SESSION['lang']['notifinputcurrency']; ?>";
</script>
<script language=javascript src=js/zMaster.js></script> 
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/generic.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_kasbank.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_efil.js?v=<?php echo time(); ?>'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>


<?php

#=== Prep Control & Search
$ctl = array();

# Control
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";



$whereJam=" kasbank=1 and detail=1 and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
if($_SESSION['language']=='EN'){
	$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam,null,true);
}else{
	$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam,null,true);
}



$optsupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier','',2,true);



$optposting[0]='Belum Diajukan';
$optposting['']=$_SESSION['lang']['all'];
$optposting[1]='Disetujui';
$optposting[3]='Ditolak';
$optposting[9]='Proses Persetujuan';

$optTipe=array(''=>'','M'=>'Masuk','K'=>'Keluar');

// $optposting=array(''=>$_SESSION['lang']['all'],'0'=>'Belum Diajukan','1'=>'Disetujui','3'=>'Ditolak','9'=>'Proses Persetujuan');
//$optTipe = makeOption($dbname,'keu_kasbankht','tipetransaksi,tipetransaksi','',null,true);
// 0; belum proses; 1:disetujui, 2 dikoreksi;3:ditolak;9:proses pengajuan


# Search
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>
<table border=0>
	<tr>
		<td>".makeElement('sNoTrans','label',$_SESSION['lang']['notransaksi'])."</td>
		<td>".makeElement('sNoTrans','text','',array('style'=>'width:100px'))."</td>
		
		<td style='padding-left:20px;'>".makeElement('sAkun','label',$_SESSION['lang']['noakun'])."</td>
		<td>".makeElement('sAkun','select','',array('style'=>'width:105px'),$optAkun)."</td>
		
		<td style='padding-left:20px;'>".makeElement('sRupiah','label',$_SESSION['lang']['jumlah'])."</td>
		<td>". makeElement('sRupiah','text','',array('style'=>'width:100px'))."</td>

		<td style='padding-left:20px;'>".makeElement('sKeterangan1','label',$_SESSION['lang']['noinvoice'])."</td>
		<td>". makeElement('sKeterangan1','text','',array('style'=>'width:100px','autocomplete'=>'off'))."</td>
	</tr>
	
	<tr>
		<td>".makeElement('sTanggal','label',$_SESSION['lang']['tanggalinput'].' Mulai')."</td>
		<td>".makeElement('sTanggal','date','',array('style'=>'width:100px'))."</td>
		
		<td style='padding-left:20px;'>".makeElement('sTanggal2','label',$_SESSION['lang']['tanggalinput'].' Selesai')."</td>
		<td>".makeElement('sTanggal2','date','',array('style'=>'width:100px'))."</td>
		
		<td style='padding-left:20px;'>".makeElement('sTipe','label',$_SESSION['lang']['tipe'])."</td>
		<td>".makeElement('sTipe','select','',array('style'=>'width:105px'),$optTipe)."</td>

		<td style='padding-left:20px;'>".makeElement('sKeterangan','label',$_SESSION['lang']['remark'])."</td>
		<td>". makeElement('sKeterangan','text','',array('style'=>'width:100px','autocomplete'=>'off'))."</td>
	</tr>
		<td>".makeElement('sPosting','label',$_SESSION['lang']['status'])."</td>
		<td>".makeElement('sPosting','select','',array('style'=>'width:105px'),$optposting,'')."</td>
		
		<td style='padding-left:20px;'>".makeElement('sSupplier','label',$_SESSION['lang']['supplier'])."</td>
		<td>".makeElement('sSupplier','selectsearch','',array('style'=>'width:105px'),$optsupplier)."</td>
		
		<td style='padding-left:20px;'>".makeElement('sBayarke','label',$_SESSION['lang']['bayarke'])."</td>
		<td>". makeElement('sBayarke','text','',array('style'=>'width:100px'))."</td>

		
		
	<tr>
	</tr>
	
	<tr>
		<td><td>".makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans()"))."</td>
	</tr>
</table>
</fieldset>";


#=== Table Aktivitas
# Header & Align
$header = array(
    $_SESSION['lang']['notransaksi'],$_SESSION['lang']['unitkerja'],
	$_SESSION['lang']['tanggalinput'],$_SESSION['lang']['noakun'],
	$_SESSION['lang']['tipe'],$_SESSION['lang']['matauang'],
	$_SESSION['lang']['jumlah'],'Balance','Bayar Kepada',$_SESSION['lang']['remark'],$_SESSION['lang']['posting'],$_SESSION['lang']['updateby']
);
$align = explode(',','C,L,C,L,C,C,R,C');

# Content
// $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";

/*
if($_SESSION['empl']['tipelokasitugas']=='KEBUN' || $_SESSION['empl']['tipelokasitugas']=='PABRIK'){
	$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
}else{
	$where = "kodeorg in (".getOrgDetail(2).")";
}
*/

$where=" 1=1 ";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	 $where.= " and kodeorg in (".getOrgDetail(2).")";
}else{
  $where.= "and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
}

// echo"<pre>";
// print_r($_SESSION['empl']);

// echo"</pre>";
// print_r($where);


$cols = "notransaksi,kodeorg,tanggalinput,noakun,tipetransaksi,matauang,jumlah,'balan',bayarkepada,keterangan,posting,userid";
$query = selectQuery($dbname,'keu_kasbankht',$cols,$where,"tanggalinput desc, notransaksi desc",false,10,1);
$data = fetchData($query);
$totalRow = getTotalRow($dbname,'keu_kasbankht',$where);
$whereAkun="";$whereOrg="";$i=0;
foreach($data as $key=>$row) {
	$optefill = makeOption($dbname,'filemanager','namafile,id',"namafile='".$row['notransaksi']."'");
	@$idefill = $optefill[$row['notransaksi']];
	
	if($idefill==''){
		$data[$key]['noSwitchList'][]="detailefill";
	}
	
	/*
	#ambil level terakhir persetujuan
	$str=" select max(level) as level from ".$dbname.".setup_approval where kodeunit='".$row['kodeorg']."' and jenispersetujuan='KASBANK'";
	$level = fetchData($str);
		
	#apakah sudah disetujui atau belum
	$str=" select count(*) as setuju from ".$dbname.".approval where notransaksi='".$row['notransaksi']."' and jenispersetujuan='KASBANK' and level='".$level[0]['level']."' and status='1'";
	$setuju = fetchData($str);
	
	#apakah sudah ada jurnal
	
	$queryJ = selectQuery($dbname,'keu_jurnaldt_vw',"*","noreferensi='".$row['notransaksi']."' and nodok not in (select notransaksi from ".$dbname.".keu_kasbankdt where kodeorg='".$row['kodeorg']."')");
	$dataJ = fetchData($queryJ);
	
	if($setuju[0]['setuju']=='0' or count($dataJ) > 0 ){
		$data[$key]['noSwitchList'][]="bayar";
	}
	*/
	
    if($row['posting']==1) {
		$data[$key]['switched']=true;
		$data[$key]['noSwitchList'][]="showEdit";
		$data[$key]['noSwitchList'][]="deleteData";
	} else if ($row['posting']==9){
		#= ajukan
		$data[$key]['noSwitchList'][]="showEdit";
		$data[$key]['noSwitchList'][]="deleteData";
		$data[$key]['noSwitchList'][]="postingData";//hilangkan tombol jika masuk aproval
	} else {
		#= jika posting=0 (masih dibuat) atau 3 (ditolak)
		// if($_SESSION['standard']['userid']==$row['userid']){
		// } else {
		// 	$data[$key]['noSwitchList'][]="showEdit";
		// 	$data[$key]['noSwitchList'][]="deleteData";
		// }
	}
	
    $data[$key]['tanggalinput'] = tanggalnormal($row['tanggalinput']);
    // unset($data[$key]['posting']);\
	
	
	if($data[$key]['posting']==3 || $data[$key]['posting']==2){
		$data[$key]['posting'] = "<font color=red><b>".$optposting[$row['posting']]."<font>";
	}else if($data[$key]['posting']==1){
		$data[$key]['posting'] = "<font color=green><b>".$optposting[$row['posting']]."<font>";
	}else{
		$data[$key]['posting'] = $optposting[$row['posting']];
	}
	
	
	
    
    # Build Condition
    if($i==0) {
		$whereAkun.="noakun='".$row['noakun']."'";
		$whereOrg.="kodeorganisasi='".$row['kodeorg']."'";
    } else {
		$whereAkun.=" or noakun='".$row['noakun']."'";
		$whereOrg.=" or kodeorganisasi='".$row['kodeorg']."'";
    }
    $i++;
}

# Posting --> Jabatan
$postJabatan = getPostingJabatan('keuangan');

# Options
if($_SESSION['language']=='EN'){
	$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereAkun);
}else{
	$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
}

$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);

# Mask Data Show
$dataShow = $data;
foreach($dataShow as $key=>$row) {
	$optNamaKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$row['userid']."'");
	$dataShow[$key]['userid'] = @$optNamaKary[$row['userid']];
    $dataShow[$key]['jumlah'] = number_format($row['jumlah'],2);
    $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
    $dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
    #=====================tambahan ginting sebagai pembalance
    $str="select sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt 
          where notransaksi='".$data[$key]['notransaksi']."' 
          and kodeorg='".$data[$key]['kodeorg']."' 
          and tipetransaksi='".$data[$key]['tipetransaksi']."'
          and noakun2a='".$data[$key]['noakun']."'";
    $res=$owlPDO->query($str);
    $res->setFetchMode(PDO::FETCH_OBJ);
    $bar=$res->fetch();
    $balan=0;
    $balan=$bar->jumlah;
    $balan=$balan-$row['jumlah'];
    #==================================
    $dataShow[$key]['balan'] = number_format($balan,2);    
}

	
	
# Make Table
$tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
#$tHeader->addAction('showDetail','Detail','images/'.$_SESSION['theme']."/detail.png");
$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
#$tHeader->addAction('approveData','Approve','images/'.$_SESSION['theme']."/approve.png");
$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan) and $_SESSION['empl']['tipelokasitugas']!='HOLDING') {
	$tHeader->_actions[2]->_name='';
}
$tHeader->addAction('detailPDF','Print Voucher','images/'.$_SESSION['theme']."/pdf.jpg");
$tHeader->_actions[3]->addAttr('event');
$tHeader->addAction('tampilDetail','Print Data Detail','images/'.$_SESSION['theme']."/zoom.png");
$tHeader->_actions[4]->addAttr('event');

$tHeader->addAction('detailefill','E-Filling System','images/efill.png');
$tHeader->_actions[5]->addAttr('event');

// $tHeader->addAction('bayar','Bayar !!!',"images/bayar.png");
// $tHeader->_actions[6]->addAttr('event');


// $tHeader->addAction('detailPDF3','Print Voucher','images/'.$_SESSION['theme']."/pdf.jpg");
// $tHeader->_actions[6]->addAttr('event');

$tHeader->pageSetting(1,$totalRow,10);
// $tHeader->_switchException = array('detailPDF','detailPDF2','tampilDetail','detailPDF3','detailefill','bayar');
$tHeader->_switchException = array('detailPDF','detailPDF2','tampilDetail','detailPDF3','detailefill');
$tHeader->setAlign($align);
$tHeader->_printXls = true;

#=== Display View
# Title & Control
OPEN_BOX('','<span class=judul>'.getMenu('keu_kasbank').'</span>');

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
<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

#=== Start ===
echo open_body();
?>
<!-- Includes -->
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript src='js/keu_3posting.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>


<?php
#====== Controller ======
# Options
//$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
//    "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
$optOrg1 = array(""=>$_SESSION['lang']['pilihdata']);
$optOrg2 = getOrgDetail(9);
$optOrg = array_merge($optOrg1,$optOrg2);
// print_r($optOrg);
// $bulantahun = $_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan'];
// // $bulantahun = "2018-06";
// $optPeriod = array($bulantahun=>$bulantahun);

// $str = "select a.namauser, b.kodeorganisasi, b.namaorganisasi FROM 
// ".$dbname.".user_orgdetail a, ".$dbname.".organisasi b
// where a.kodeorganisasi=b.kodeorganisasi and a.namauser='admin.owl'";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()) {
//    $optOrg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }
// // print_r($optPeriod);
$optPeriod= array(""=>$_SESSION['lang']['pilihdata']);
// $optPeriod = makeOption($dbname, 'setup_periodeakuntansi','kodeorg,periode,tutupbuku',"kodeorg='TPRM' ");

//$optJenisData=array('gudang'=>'gudang - (RO,Kebun,PKS)','gaji'=>'Gaji Karyawan Tidak Langsung - (RO,Traksi,Kebun,PKS)','gajiharilibur'=>'Gaji Karyawan Langsung - (Kebun)','alokasi'=>'Alokasi Traksi - (Traksi)','depresiasi'=>'Depresiasi');
if($_SESSION['language']=='EN'){
    $optJenisData=array(
		'gaji'              =>'1. Salaries From General Cost',
		'alokasi_idle'      =>'2. Idle Allocation Salaries (Traksi & Workshop)',
		'gajitrk'           =>'3. Allocation Salaries (Traksi & Workshop)',
		'gajiharilibur'     =>'4. UnAllocated Salaries of Plant Labour',
		'potongan'          =>'5. Deduction Journal',
		'depresiasi'        =>'6. Depreciation',
		// 'fixtrans'       =>'Warehouse Integrity Check',        
		'asuransi'          =>'7. Recurring',
		'alokasi'           =>'8. Vehicle Running Allocation - (Traksi & Workshop)',
		'bibit'             =>'9. Alokasi bibit PN ke MN',
		// 'tbsramp'        =>'TBS Ramp Differentiation',
		// 'hppolah'        =>'HPP Olah TBS',
		// 'millmaintenance'=>'6. Mill Maintenance',
		'kurs'              =>'10. Kurs Differentiation',
		//'mutasi'          =>'Promotion / Mutation / Demotion',
		'gajiho'            =>'11. Salaries Employee HO'
	);//'sipilalokasi'=>'Housing allocation',
}  else  {
    $optJenisData=array(
		'gaji'              =>'1. Gaji Karyawan Tidak Langsung - (RO,Kebun,PKS)',
		'alokasi_idle'      =>'2. Alokasi Traksi dan Bengkel Idle',
		'gajitrk'           =>'3. Gaji Karyawan Tidak Langsung - (Traksi,Bengkel)',
		'gajiharilibur'     =>'4. Gaji Karyawan Belum Teralokasi',
		// 'gajiharilibur'  =>'2. Gaji Karyawan Langsung - (Kebun)',
		'potongan'          =>'5. Penarikan Potongan Gaji Karyawan',
		'depresiasi'        =>'6. Depresiasi',
		// 'fixtrans'       =>'Integrity Check Gudang',          
		'asuransi'          =>'7. Transaksi Berulang/Recurring',
		// 'millmaintenance'=>'6. Mill Maintenance',
		'alokasi'           =>'8. Alokasi Traksi dan Bengkel',
		'bibit'             =>'9. Alokasi bibit PN ke MN',
		'alkbibit'          =>'10. Alokasi bibit ke Lapangan',
		// 'tbsramp'        =>'Selisih TBS Ramp',
		// 'hppolah'        =>'HPP Olah TBS',
		'kurs'              =>'11. Selisih Kurs',
		//'mutasi'          =>'Promosi / Mutasi / Demosi',
		'gajiho'            =>'12. Penggajian HO'
	);//'sipilalokasi'=>'Alokasi Perumahaan',
}
# Fields
$els = array();
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','selectsearch','',array('style'=>'width:200px','onchange'=>'changeperiode(this)'),$optOrg)
);
$els[] = array(
  makeElement('periode','label',$_SESSION['lang']['periode']),
  makeElement('periode','select','',array('style'=>'width:200px'),$optPeriod)
);
$els[] = array(
  makeElement('jenisData','label',$_SESSION['lang']['jenisbiaya']),
  makeElement('jenisData','select','',array('style'=>'width:200px'),$optJenisData)
);

# Button
$els['btn'] = array(
  makeElement('btnList','button',$_SESSION['lang']['list'],array('onclick'=>'listPosting()'))
);

#====== View ======
# Menu
include('master_mainMenu.php');

# Form
// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['peosesakhirbulan']).'</span><br>');
OPEN_BOX('','<span class=judul>'.getMenu('keu_3posting').'</span><br>');
echo genElTitle($_SESSION['lang']['form'],$els);

// echo"<fieldset style=float:left>
     // <legend><b>".$_SESSION['lang']['form']."</b></legend>
	 // <table>
		// <tr>
			// <td>".makeElement('kodeorg','label',$_SESSION['lang']['kodeorg'])."</td>
			// <td>:</td>
			// <td>".makeElement('kodeorg','selectsearch','',array('style'=>'width:200px','onchange'=>'changeperiode(this)'),$optOrg)."</td>
		// </tr>
		// <tr>
			// <td>".makeElement('periode','label',$_SESSION['lang']['periode'])."</td>
			// <td>:</td>
			// <td>".makeElement('periode','select','',array('style'=>'width:200px'),$optPeriod)."</td>
		// </tr>
		// <tr>
			// <td>".makeElement('jenisData','label',$_SESSION['lang']['jenisbiaya'])."</td>
			// <td>:</td>
			// <td>".makeElement('jenisData','select','',array('style'=>'width:200px'),$optJenisData)."</td>
		// </tr>
		// <tr>
			// <td></td>
			// <td></td>
			// <td>".makeElement('btnList','button',$_SESSION['lang']['list'],array('onclick'=>'listPosting()'))."
			// ".makeElement('btnExcel','button',$_SESSION['lang']['excel'],array("onclick"=>"listPosting('excel')"))."</td>
		// ";
		
// echo"</tr>
	 // </table>
     // </fieldset>";
	
CLOSE_BOX();

# List
// OPEN_BOX();
OPEN_BOX('','');
echo "<div id=listPosting style=height:60vh></div>";
//echo makeFieldset2($_SESSION['lang']['list'],'listPosting',null,true,'auto','325px');
CLOSE_BOX();

#=== End ===
close_body();
?>
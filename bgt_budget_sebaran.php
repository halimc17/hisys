<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script>
pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script language="javascript" src="js/zTools.js"></script>
<script type="text/javascript" src="js/bgt_budget_sebaran.js"></script>
<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
</script>
<?php

// Options
$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					  "LENGTH(kodeorganisasi)=4",2);
$optDiv = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					"induk='".key($optUnit)."'",2);
$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					  "induk='".key($optDiv)."'",2);
$optKode = makeOption($dbname,'bgt_kode','kodebudget,nama',
					  "kodebudget not in ('KONTRAK','TOOL')",2,true);
$optKode[''] = $_SESSION['lang']['pilihdata'];
$optMethod = array('insert'=>'Insert','update'=>'Update');

// Onchange Organisasi
$qOrg = selectQuery($dbname,'organisasi','kodeorganisasi,namaorganisasi,induk',
					 "LENGTH(kodeorganisasi) > 5");
$resOrg = fetchData($qOrg);
$optOrg = array();
foreach($resOrg as $row) {
	if(strlen($row['kodeorganisasi'])==6) {
		if(!isset($optOrg[substr($row['kodeorganisasi'],0,4)][$row['kodeorganisasi']])) {
			$optOrg[substr($row['kodeorganisasi'],0,4)][$row['kodeorganisasi']] = array(
				'nama' => $row['kodeorganisasi']." - ".$row['namaorganisasi'],
				'child' => array()
			);
		} else {
			$optOrg[substr($row['kodeorganisasi'],0,4)][$row['kodeorganisasi']]['nama'] =
				$row['kodeorganisasi']." - ".$row['namaorganisasi'];
		}
	} else {
		if(!isset($optOrg[substr($row['kodeorganisasi'],0,4)][substr($row['kodeorganisasi'],0,6)])) {
			$optOrg[substr($row['kodeorganisasi'],0,4)][substr($row['kodeorganisasi'],0,6)] = array(
				'nama' => '',
				'child' => array($row['kodeorganisasi'] => $row['kodeorganisasi']." - ".$row['namaorganisasi'])
			);
		} else {
			$optOrg[substr($row['kodeorganisasi'],0,4)]
				[substr($row['kodeorganisasi'],0,6)]
				['child'][$row['kodeorganisasi']] = $row['kodeorganisasi']." - ".$row['namaorganisasi'];
		}
	}
}

// Panjang Field
$attr = array('style'=>'width:200px');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['sebaran'].' '.$_SESSION['lang']['anggaran']).'</span>');
?>
<input id='refOrg' type='hidden' value='<?php echo json_encode($optOrg)?>' >
<fieldset><legend><?php echo $_SESSION['lang']['form']?></legend>
<table>
	<tr>
		<td><?php echo $_SESSION['lang']['budgetyear']?></td>
		<td><?php echo makeElement('tahunbudget','textnum',"",
								   array('style'=>'width:200px','onchange'=>"getList()"))?></td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['unit']?></td>
		<td><?php echo makeElement('unit','select',"",
								   array('style'=>'width:200px','onchange'=>'changeUnit()'),
								   $optUnit)?></td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['divisi']?></td>
		<td><?php echo makeElement('divisi','select',"",
								   array('style'=>'width:200px','onchange'=>'changeDiv()'),
								   $optDiv)?></td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['blok']?></td>
		<td><?php echo makeElement('blok','select',"",
								   array('style'=>'width:200px','onchange'=>"getList()"),
								   $optBlok)?></td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['tipe']?></td>
		<td><?php echo makeElement('kode','select',"",
								   array('style'=>'width:200px','onchange'=>"getList()"),
								   $optKode)?>
			<span>Pilih tipe untuk menampilkan List</span>
		</td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['sebaran']?></td>
		<td>
			<table>
				<tr>
					<td align=center>Jan</td><td align=center>Feb</td><td align=center>Mar</td>
					<td align=center>Apr</td><td align=center>Mei</td><td align=center>Jun</td>
					<td align=center>Jul</td><td align=center>Agt</td><td align=center>Sep</td>
					<td align=center>Okt</td><td align=center>Nov</td><td align=center>Des</td>
				</tr>
				<tr>
					<td><?php echo makeElement('sebar01','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar02','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar03','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar04','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar05','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar06','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar07','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar08','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar09','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar10','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar11','textnum',1,array('style'=>'width:50px'))?></td>
					<td><?php echo makeElement('sebar12','textnum',1,array('style'=>'width:50px'))?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</fieldset>
<?php CLOSE_BOX();
OPEN_BOX();?>
<fieldset><legend><?php echo $_SESSION['lang']['list']?></legend>
<div id='container' style='display:block'></div>
</fieldset>
<?php CLOSE_BOX();
echo close_body();
?>
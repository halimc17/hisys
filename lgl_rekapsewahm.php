<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include('lib/zFunction.php');
	echo open_body();
	include('master_mainMenu.php');
    require_once('lib/zSelect2.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/lgl_rekapsewahm.js?v=<?php echo time(); ?>></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});

	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php
	$optorg=$optorg2=$optseluruhnya=$optStat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// $optseluruhnya	 	="<option value=''>".$_SESSION['lang']['all']."</option>";

	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$wh = "";
	} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$wh = "";
	}else{	
		$wh= " and kodeorganisasi in (".getOrgDetail(2).")";
	}
	$wh= " and kodeorganisasi in (".getOrgDetail(2).")";
	
	
	# Organisasi
	$optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
	$optorg2 = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh."";
	$res = fetchData($str);
	foreach($res as $key => $val){
		if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
			$optorg.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
			$optorg2.="<option value=".$val['kodeorganisasi']." selected >".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}else{		
			$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
	}
	$arrStat=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
	foreach($arrStat as $rw=>$lst){
		$optStat.="<option value='".$rw."'>".$lst."</option>";
	}

	$optsupp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a 
	left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi 
	where a.posting='0' and b.jenis='SEWA.HM' and a.kodeorg in (".getOrgDetail(2).") order by a.notransaksi asc";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	$namasupp=array();
	while ($bar = $qry->fetch()) {
		$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
		
		$optsupp.="<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
	}

	$optprd = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$optprdscr = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
	$sql = "SELECT distinct(substr(tanggal,1,7)) as periode FROM " . $dbname . ".vhc_runht order by periode desc limit 12 ";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $qry->fetch()) {
		$optprd.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
		$optprdscr.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
	}
	
	$res=array('1'=>'1 (Pertama) ', '2'=>'2 (Kedua) ','3'=>'3 (Ketiga) ','4'=>'4 (Keempat)','5'=>'5 (Kelima)');
	// $res=array('0'=>'Sebulan (Tanggal : 1 s/d 30)','1'=>'Pertama (Tanggal : 1 s/d 15)','2'=>'Kedua (Tanggal : 16 s/d 30)');
	$optbyr="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	foreach($res as $key => $val){
		$optbyr.="<option value=".$key.">".$val."</option>";
	}
?>

<!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
<?php
	OPEN_BOX('','<span class=judul>'.getMenu('lgl_rekapsewahm').'</span>');
	echo"<div>";
	echo   "<table cellspacing=1 border=0>
				<tbody>
					<tr valign=middle>
						<td style=width:100px;cursor:pointer; onclick=createNew() align=center>
							<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>
							".$_SESSION['lang']['new']."
						</td>
						<td style=width:100px;cursor:pointer; onclick=displayList() align=center>
							<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>
							".$_SESSION['lang']['list']."
							<td>
						</td>
						<td>
							<fieldset style='width:auto;'>
								<legend>".$_SESSION['lang']['find']."</legend>
								<table>
                                    <tr>
                                        <td>" . $_SESSION['lang']['unit'] . "</td> 
                                        <td>:</td>
                                        <td><select class=select2 id=divsch  style=\"width:150px;\">" . $optorg . "</select></td>
                                        
                                        <td>" . $_SESSION['lang']['spk'] . "</td> 
                                        <td>:</td>
                                        <td><input class=myinputtext id=nospkcr onkeypress='enterkey(event,loadData)' style=\"width:150px;\"></td>

                                        <td>" . $_SESSION['lang']['bulan'] . "</td> 
                                        <td>:</td>
                                        <td><select class=select2 id=tglsch  style=\"width:150px;\">" . $optprdscr . "</select></td>
                                    </tr>
                                    <tr hidden>
                                        <td>" . $_SESSION['lang']['kontraktor'] . "</td> 
                                        <td>:</td>
                                        <td><input class=myinputtext id=kontrakcr onkeypress='enterkey(event,loadData)' style=\"width:150px;\"></td>
                                    </tr>
									<tr>
										<td></td>
										<td></td>
										<td>
											<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
											<button class=mybutton onclick=displayList()>".$_SESSION['lang']['cancel']."</button>
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>";
	echo"</div>";
	CLOSE_BOX();
?>

<!-------------------------------- LIST DATA --------------------------------------->
<?php
	echo "<div id=listData>";
	OPEN_BOX();
	echo "<div class=table-scroll>
			<table class=sortable cellspacing=1 cellpadding=5 border=0>
				<thead>
					<tr class=rowheader>
						<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center>" . $_SESSION['lang']['unit'] . "</th>
						<th align=center>" . $_SESSION['lang']['bulan'] . "</th>
						<th align=center>" . $_SESSION['lang']['periode'] . "</th>
						<th align=center>" . $_SESSION['lang']['nospk'] . "</th>
						<th align=center>" . $_SESSION['lang']['notransaksi'] . " Traksi</th>
						<th align=center>" . $_SESSION['lang']['kontraktor'] . "</th>
						<th align=center>" . $_SESSION['lang']['rupiah'] . "</th> 
						<th align=center>No BAPP</th> 
						<th align=center colspan=3>" . $_SESSION['lang']['action'] . "</th>
					</tr>
				</thead>
				<tbody id=container>
					<script>loadData(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>";
	CLOSE_BOX();
	echo"</div>";
?>

<!------------------------- Buat Baru  --------------------------->
<?php
	echo "<div id=addNew style=display:none>";
	OPEN_BOX();
	echo 	"<fieldset style='float:left; widht:auto;'>
				<legend>" . $_SESSION['lang']['entryForm'] . "</b></legend> 
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
					<tr>
						<td style=\"width:100px;\">" . $_SESSION['lang']['kodeorg'] . "</td> 
						<td>:</td>
						<td><select class=select2 style=\"width:230px;\" id=kodeorg>" . $optorg2 . "</select></td>
					</tr> 
					<tr>
						<td>" . $_SESSION['lang']['bulan'] . "</td> 
						<td>:</td>
						<td><select class=select2 style=\"width:230px;\" id=periode onchange=getnospk()>" . $optprd . "</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['periode'] . "</td> 
						<td>:</td>
						<td><input type='text' readonly=readonly style='width:100px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false'; /> s/d <input type='text' readonly=readonly style='width:100px;' class='myinputtext' id='tglselesai' onmousemove='setCalendar(this.id)' onkeypress='return false'; /></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['termin'] . "</td> 
						<td>:</td>
						<td><select class=select2 style=\"width:230px;\" id=periodebyr >" . $optbyr . "</select></td>
					</tr>
					<tr style=display:none>
						<td>" . $_SESSION['lang']['tanggal'] . "</td> 
						<td>:</td>
						<td><input type=text class=myinputtext placeholder='Seluruhnya' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:145px;\" readonly/></td>
						
					</tr>
					<tr>
						<td>No SPK</td> 
						<td>:</td>
						<td><select class=select2 style=\"width:230px;\" id=spk>" . $optsupp . "</select>
							</td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td>
							<input type=hidden id=method value='insert'>
							<button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
							<button class=mybutton onclick=hapus()>" . $_SESSION['lang']['cancel'] . "</button>
						</td>
					</tr>
				</table>
			</fieldset>";
			CLOSE_BOX();
		echo"</div>";
?>

<!-------------------------- Detail ------------------------------>
<?php 
	echo"<div id=detail style='display:none;';>";
	OPEN_BOX();
	CLOSE_BOX();
	echo"</div>";
echo close_body();
?>
<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include('lib/zFunction.php');
	echo open_body();
	include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/pabrik_stockforsale.js?v=<?php echo time(); ?>></script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php
	$optpabrik=$optseluruhnya=$optschbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sUnit	="SELECT namaorganisasi,kodeorganisasi,induk,tipe FROM ".$dbname.".organisasi WHERE tipe in ('PABRIK') and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ORDER BY kodeorganisasi";
	$qUnit	=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
	$qUnit->setFetchMode(PDO::FETCH_ASSOC);
	while($rUnit=$qUnit->fetch()){
		$optpabrik    .="<option value=".$rUnit['kodeorganisasi'].">".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
	}
	

	$optbarang="";
	$barang = array('CPO' => 'CPO','KER' => 'KERNEL');
	foreach ($barang as $key => $value) {
		$optschbarang.="<option value=".$key.">".$value."</option>";
		$optbarang.="<option value=".$key.">".$value."</option>";
	}
?>

<!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
<?php
    OPEN_BOX('','<span class=judul>'.getMenu('pabrik_stockforsale').'</span><br>');
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
										<td align=left>".$_SESSION['lang']['pabrik']."</td>
										<td>:</td>
										<td><select id=kodeorgsch onchange=loadData(); style='width:200px;'>".$optpabrik."</select></td>

										<td align=left>".$_SESSION['lang']['barang']."</td>
										<td>:</td>
										<td><select id=barangsch onchange=loadData(); style='width:200px;'>".$optschbarang."</select></td>

										<td>".$_SESSION['lang']['tanggal']."</td>
										<td>:</td>		
										<td><input type=text class=myinputtext id=tanggalsch name=tanggalsch onmousemove=setCalendar(this.id) onkeypress=return false; onchange=loadData();  maxlength=10 style=width:58px; readonly/></td>

										<td></td>
										<td></td>
										<td>
											<button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button>
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
	echo " 	
				<div>
					<table class=sortable cellspacing=1 border=0 cellpadding=5>
						<thead>
							<tr class=rowheader>
								<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
								<th align=center>" . $_SESSION['lang']['pabrik'] . "</th>
								<th align=center>" . $_SESSION['lang']['barang'] . "</th>
								<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
								<th align=center>" . $_SESSION['lang']['updateby'] . "</th>  
								<th align=center colspan=2>" . $_SESSION['lang']['action'] . "</th>
							</tr>
						</thead>
						<tbody id=container>
							<script>loadData(0)</script>
						</tbody>
						<tfoot id=footData>
						</tfoot>
					</table>
				</div>
			";
	CLOSE_BOX();
	echo"</div>";
?>

<!------------------------- Buat Baru Stock For Sale --------------------------->
<?php
	echo "<div id=addNew style=display:none>";
	OPEN_BOX();
	echo 	"<fieldset style='float:left; widht:auto;'>
				<legend>" . $_SESSION['lang']['entryForm'] . "</b></legend> 
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>" . $_SESSION['lang']['pabrik']."</td>
						<td>:</td>
						<td><select id=kodeorg style='width:150px;'>".$optpabrik."</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['barang']."</td>
						<td>:</td>
						<td><select id=barang style='width:150px;'>".$optbarang."</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['tanggal'] . "</td>
						<td>:</td>
						<td><input autocomplete=off type=text class=myinputtext id=tanggal name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10  maxlength=10 style=width:145px;/ readonly></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td>
							<input type=hidden id=method value='insert'>
							<button class=mybutton onclick=preview()>" . $_SESSION['lang']['preview'] . "</button>
							<button class=mybutton onclick=hapus()>" . $_SESSION['lang']['cancel'] . "</button>
						</td>
					</tr>
				</table>
			</fieldset>";
			CLOSE_BOX();
		echo"</div>";
?>

<!------------------------- Detail Data --------------------------->
<?php
	echo "<div id=listdetail style=display:none>";
        OPEN_BOX();
        echo "<div id=containerdetail>";
        echo "</div>";
        CLOSE_BOX();
    echo"</div>";
    echo close_body();
?>
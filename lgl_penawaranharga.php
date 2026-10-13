<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include('lib/zFunction.php');
	echo open_body();
	require_once('lib/zSelect2.php');
	include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/lgl_penawaranharga.js?v=<?php echo time(); ?>></script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php
	$optsup=$optseluruhnya=$optproj="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
	$sSup="	SELECT a.*,b.* FROM $dbname.log_5supkelompok a 
				LEFT JOIN $dbname.log_5supplier b 
				ON a.supplierid=b.supplierid 
				WHERE a.tipe='KONTRAKTOR' AND b.status='1' 
				ORDER BY namasupplier";
	$rSup=	fetchData($sSup);
	foreach ($rSup as $v)
	{
		$optsup	.="<option value='".$v['supplierid']."'>".$v['namasupplier']."</option>";
	}

	#opsi filter daftar: unit sesuai detail akses user, status dari kamus status
	$optunitsch = "<option value=''>".$_SESSION['lang']['all']."</option>";
	foreach (getOrgDetail(1) as $kodeunit=>$namaunit)
	{
		$optunitsch .= "<option value='".$kodeunit."'>".$kodeunit." - ".$namaunit."</option>";
	}
	$optstatussch = "<option value=''>".$_SESSION['lang']['all']."</option>";
	foreach ($arrstatus as $kodestatus=>$namastatus)
	{
		$optstatussch .= "<option value='".$kodestatus."'>".strip_tags($namastatus)."</option>";
	}
	$str=selectQuery($dbname,'project','*',"posting='0' and pekerjaan='External'");
	$res=fetchData($str);
	foreach ($res as $v)
	{
		$optproj	.="<option value='".$v['kode']."'>".$v['kode']." - ".$v['nama']."</option>";
	}
?>

<!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
<?php
	OPEN_BOX('','<span class=judul>'.getMenu('lgl_penawaranharga').'</span>');
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
											<td nowrap>".$_SESSION['lang']['notransaksi']."</td>
											<td>:</td>
											<td><input type=text id=notransaksisch onkeyup=loadData(0); maxlength=30 class=myinputtext style=\"width:180px;box-sizing:border-box;\"></td>

											<td nowrap>".$_SESSION['lang']['tanggal']."</td>
											<td>:</td>
											<td><input type=text class=myinputtext id=tanggalsch name=tanggalsch onmousemove=setCalendar(this.id) onkeypress=return false; onchange=loadData(0); maxlength=10 style=\"width:180px;box-sizing:border-box;\" readonly/></td>
										</tr>
										<tr>
											<td nowrap>".$_SESSION['lang']['unit']."</td>
											<td>:</td>
											<td><select id=kodeorgsch class=select2 onchange=loadData(0) style=width:180px;>".$optunitsch."</select></td>

											<td nowrap>".$_SESSION['lang']['status']."</td>
											<td>:</td>
											<td><select id=statussch class=select2 onchange=loadData(0) style=width:180px;>".$optstatussch."</select></td>
										</tr>
										<tr>
											<td colspan=6>
												<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
												<button class=mybutton onclick=exportExcelPH()>".$_SESSION['lang']['excel']."</button>
												<button class=mybutton onclick=exportPdfPH()>PDF</button>
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
	echo " 	<fieldset style='width:auto;'>
				<legend>" . $_SESSION['lang']['list'] . "</legend>
				<div>
					<table class=sortable cellspacing=1 cellpadding=5 border=0 style='width:100%;'>
						<thead>
							<tr class=rowheader>
								<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
								<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
								<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
								<td align=center>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</td>
								<td align=center>" . $_SESSION['lang']['harga'] . "</td>
								<td align=center>" . $_SESSION['lang']['status'] . "</td>
								<td align=center colspan=7>" . $_SESSION['lang']['action'] . "</td>
							</tr>
						</thead>
						<tbody id=container>
							<script>loadData(0)</script>
						</tbody>
						<tfoot id=footData>
						</tfoot>
					</table>
				</div>
			</fieldset>";
	CLOSE_BOX();
	echo"</div>";
?>

<!-------------------------- Buat Baru Penawaran Harga ---------------------------->
<?php
	echo "<div id=addNew style=display:none>";
	OPEN_BOX();
	echo 	"<fieldset style='float:left; width:auto;'>
				<legend>" . $_SESSION['lang']['form'] . " ".$_SESSION['lang']['input']."</legend> 
				<table border=0 cellpadding=2 cellspacing=1>
						<tr>
							<td align=left>".$_SESSION['lang']['notransaksi']."</td>
							<td>:</td>
							<td><input type=text id=notransaksi maxlength=20 class=myinputtext style=\"width:250px;box-sizing:border-box;\" disabled></td>

							<td style='padding-left:20px'>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
							<td><input type=text class=myinputtext id=tanggal name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=\"width:250px;box-sizing:border-box;\" readonly/></td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</td>
							<td>:</td>
							<td><select id=nama class='select2' style='width:250px;'>".$optproj."</select></td>

							<td style='padding-left:20px'>" . $_SESSION['lang']['kodesupplier']."</td>
							<td>:</td>
							<td nowrap><select id=supplierid class='select2' style='width:250px;'>".$optsup."</select>&nbsp;&nbsp;
								<input type=hidden id=methodht value='saveheader'><input type=hidden id=halaman value='0'><img title=\"Tambah Kontraktor\" class=\"zImgBtn\" onclick=\"saveheader()\" src=\"images/plus.png\" style='vertical-align:middle'>
								<button id=btnnext class=mybutton onclick=bukaharga() style='display:none'>".$_SESSION['lang']['lanjut']."</button>
							</td>
						</tr>
					</table>
			</fieldset>";
			CLOSE_BOX();
		echo"</div>";
		echo "<div id=detail style='display:none'>";
		OPEN_BOX();
		echo " 	<fieldset>
					<legend>" . $_SESSION['lang']['list'] . "</legend>
					<div>
						<table class=sortable cellspacing=1 border=0>
							<thead>
								<tr class=rowheader>
									<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
									<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
									<td align=center>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</td>
									<td align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>
									<td align=center>" . $_SESSION['lang']['action'] . "</td>
								</tr>
							</thead>
							<tbody id=containerdetail>
							</tbody>
							<tfoot id=footData>
							</tfoot>
						</table>
					</div>
				</fieldset>";
		CLOSE_BOX();
		echo"</div>";
		echo "<div id=detail2 style='display:none'>";
		OPEN_BOX();
		echo " 	<fieldset>
					<legend>" . $_SESSION['lang']['form'] . " Input Perbandingan Harga</legend>
					<div id=listharga>
					</div>
				</fieldset>";
		CLOSE_BOX();
		echo"</div>";
	echo close_body();
?>
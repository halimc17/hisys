<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	include('lib/zFunction.php');
	echo open_body();
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
										<td align=left>".$_SESSION['lang']['notransaksi']."</td>
										<td>:</td>
										<td><input type=text id=notransaksisch onkeyup=loadData(); maxlength=20 class=myinputtext size=26 style=\"width:200px;\"></td>

										<td hidden>".$_SESSION['lang']['tanggal']."</td>
										<td hidden>:</td>		
										<td hidden><input type=text class=myinputtext id=tanggalsch name=tanggalsch onmousemove=setCalendar(this.id) onkeypress=return false; onchange=loadData();  maxlength=10 style=width:58px; readonly/></td>

										<td hidden align=left>".$_SESSION['lang']['kodeorganisasi']."</td>
										<td hidden>:</td>
										<td hidden><select id=kodeorgsch onchange=loadData(); style='width:300px;'>".$optxxx."</select></td>

										<td hidden align=left>".$_SESSION['lang']['status']."</td>
										<td hidden>:</td>
										<td hidden><select id=statussch onchange=loadData() style='width:80px;'>".$optStat."</select></td>

										<td></td>
										<td></td>
										<td>
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
					<table class=sortable cellspacing=1 cellpadding=5 border=0 width=50%>
						<thead>
							<tr class=rowheader>
								<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
								<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
								<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
								<td align=center>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</td>
								<td align=center>" . $_SESSION['lang']['harga'] . "</td>
								<td align=center>" . $_SESSION['lang']['status'] . "</td>
								<td align=center colspan=5>" . $_SESSION['lang']['action'] . "</td>
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
	echo 	"<fieldset style='float:left; widht:auto;'>
				<legend>" . $_SESSION['lang']['form'] . " ".$_SESSION['lang']['input']."</b></legend> 
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:middle'>
					<tr>
						<td align=left>".$_SESSION['lang']['notransaksi']."</td>
						<td>:</td>
						<td><input type=text id=notransaksi maxlength=20 class=myinputtext size=26 style=\"width:150px;\" disabled></td>
						
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>		
						<td><input type=text class=myinputtext id=tanggal name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:58px; readonly/></td>

						<td>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</td>
						<td>:</td>
						<td><select id=nama style='width:150px;'>".$optproj."</select>
						</td>
						
						<td>" . $_SESSION['lang']['kodesupplier']."</td>
						<td>:</td>
						<td><select id=supplierid style='width:150px;'>".$optsup."</select>&nbsp;&nbsp;
							<input type=hidden id=methodht value='saveheader'><input type=hidden id=halaman value='0'><img title=\"Add File Upload\" class=\"zImgBtn\" onclick=\"saveheader()\" src=\"images/plus.png\" style='vertical-align:middle'>
							
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
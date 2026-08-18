<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/keu_5caco.js?v=<?php echo time(); ?>></script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php
$optorg=$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sorg	="SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4' order by namaorganisasi";
$qorg	=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$qorg->fetch())
{
	$optorg.="<option value='".$rorg['kodeorganisasi']."'>".$rorg['kodeorganisasi']." - ".$rorg['namaorganisasi']."</option>";
}

$sakun	="SELECT * FROM ".$dbname.".keu_5akun where (noakun like '11401%' or noakun like '11402%') and detail=1 order by noakun";
$qakun	=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch())
{
	$optakun.="<option value='".$rakun['noakun']."'>".$rakun['noakun']." - ".$rakun['namaakun']."</option>";
}
?>

<!------------------- HEADER untuk BUAT BARU, LIST DATA dan CARI ------------------->
<?php
OPEN_BOX('','<span class=judul>'.getMenu('keu_5caco').'</span>');


echo"<div>";
echo "	<table cellspacing=1 border=0>
			<tbody>
				<tr valign=middle>
					
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
									<td align=left>".$_SESSION['lang']['kodeorganisasi']."</td>
									<td>:</td>
									<td><select id=kodeorgsch onchange=loaddata(); style='width:100px;'>".$optorg."</select></td>
									
									
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
		</table>
	</div>";
CLOSE_BOX();
?>

<!-------------------------------- LIST DATA --------------------------------------->
<?php
echo "<div id=listdata>";
OPEN_BOX();
echo " 	<fieldset style='float:left; width:auto;'>
			<legend>" . $_SESSION['lang']['list'] . "</legend>
			<div>
				<table class=sortable cellspacing=1 border=0>
					<thead>
						<tr class=rowheader>
							<td align=center>No</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namaorganisasi'] . "</td>
							<td align=center>" . $_SESSION['lang']['action'] . "</td>
						</tr>
					</thead>
					<tbody id=container>
						<script>loaddata(0)</script>
					</tbody>
				</table>
			</div>
		</fieldset>";
CLOSE_BOX();
echo"</div>";
?>

<!------------------------- Buat Baru AKUN INTRA/INTERCO --------------------------->
<?php
echo "<div id=input style=display:none>";

echo "</div>";
echo close_body();
?>
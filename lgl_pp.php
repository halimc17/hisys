<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/lgl_pp.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
$jenisApp = "PP";

##deklarasi untuk option##
$optorg =$optorgx=$optkat= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$where = "";
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorgx.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".lgl_kategoribansos order by nama asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optkat.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
}

$optunit =$optpt= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(kodeorg) as kodeorganisasi FROM " . $dbname . ".lgl_bansos";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}

$sql = "SELECT distinct(induk) as induk FROM " . $dbname . ".lgl_bansos a left join " . $dbname . ".organisasi b on a.kodeorg=b.kodeorganisasi";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optpt.="<option value=" . $bar['induk'] . ">" . $bar['induk'] . "</option>";
}


$optper =$opttipe= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(periode) as periode FROM " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrtipe=array('transfer'=>'TRANSFER','cash'=>'CASH');
foreach($arrtipe as $key => $val){
	$opttipe.="<option value=" . $key. ">" . $val . "</option>";
}

$optkas="<option value=''></option>";
$arrtipekas=array('1'=>'Proses','0'=>'Belum Proses');
foreach($arrtipekas as $key => $val){
	$optkas.="<option value=" . $key. ">" . $val . "</option>";
}

$optsts="<option value=''></option>";
$arrtipekasx=array('x'=>'Perlu Persetujuan','0'=>'Dalam Proses Persetujuan','1'=>'Disetujui','2'=>'Dikoreksi','3'=>'Ditolak');
foreach($arrtipekasx as $key => $val){
	$optsts.="<option value=" . $key. ">" . $val . "</option>";
}

$str=" select a.*,b.namabank as namakodebank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where a.status ='1'"; //exit('error'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while($bar=$res->fetch()){
	$optbank.="<option value=" . $bar['noakun'] . ">" . $bar['namakodebank'] . " - " . $bar['rekening'] . "</option>";
}

@$countApprove = getCountApproval('BANSOS',$kodeorg);
$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);

OPEN_BOX('','<span class=judul>'.getMenu('lgl_pp').'</span>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['pt'] . "</td> 
				<td>:</td>
				<td><select id=ptsrc  style=\"width:100px;\">" . $optpt . "</select></td>
				
				
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=divsch  style=\"width:100px;\">" . $optunit . "</select></td>
				
				<td>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['kasbank'] . "</td> 
				<td>:</td>
				<td><select id=kasbanksrc  style=\"width:100px;\">" . $optkas . "</select></td>
				
				</tr>
				<tr>
				
				<td>" . $_SESSION['lang']['tanggalmulai'] . "</td> 
				<td>:</td>
				<td><input id='tanggalmulai' type='text' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
				
				<td>" . $_SESSION['lang']['tanggalsampai'] . "</td> 
				<td>:</td>
				<td><input id='tanggalsampai' type='text' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
				
				
				<td>" . $_SESSION['lang']['kategori'] . "</td> 
				<td>:</td>
				<td><select id=katsrc  style=\"width:100px;\">" . $optkat . "</select></td>

            </tr>
			
			<tr>
		
				<td>" . $_SESSION['lang']['status'] . "</td> 
				<td>:</td>
				<td><select id=status  style=\"width:100px;\">" . $optsts . "</select></td>
				
				</td>
            </tr>
			";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
		<td>
		<fieldset style=display:none><legend>" . $_SESSION['lang']['print'] . "</legend> 
        <table>
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=unitexp  style=\"width:100px;\">" . $optunit . "</select></td>
				</tr>
				<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=perexp  style=\"width:100px;\">" . $optper . "</select></td>
			</tr>
			";

echo"<tr><td><td><td><button class=mybutton onclick=excel(event,'vhc_slave_byyijinops.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
     </tr>
	 </table> ";
echo "</div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td align=center>" . $_SESSION['lang']['periode'] . "</td>
				<td align=center>" . $_SESSION['lang']['kategori'] . "</td>
				<td align=center>" . $_SESSION['lang']['biaya'] . "</td>
				<td align=center>" . $_SESSION['lang']['kasbank'] . "</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>";
				
				$countApp = getCountApproval($jenisApp,'');
				for($i=1;$i<=$countApp;$i++){
					echo"<td align=center>".$_SESSION['lang']['persetujuan']. "".$i."</td>";
				}
				
				echo"<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
				<td align=center colspan='5'>" . $_SESSION['lang']['action'] . "</td>
		</thead>
		 <tbody id=contain> 
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";
echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
	<fieldset >
	<legend>Header</legend>
	<table cellspacing=1 border=0>
		<tr>
			<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
			<td>:</td>
			<td>
			<input id=notransaksi style='width:170px;' class='myinputtext' disabled/>
			</td>
			
			<td>" . $_SESSION['lang']['kategori'] . "</td> 
			<td>:</td>
			<td><select style=\"width:175px;\" id=kategori>" . $optkat . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
			<td>:</td>
			<td><select onchange=getnotransaksi() style=\"width:175px;\" id=kodeorg>" . $optorg . "</select></td>
			
			<td>Nama Pemesan</td> 
			<td>:</td>
			<td><input id=namapemesan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:170px;'></td>
		</tr> 
		<tr>
			<td>" . $_SESSION['lang']['tanggal'] . "</td> 
			<td>:</td>
			<td><input type=text onchange=getnotransaksi() class=myinputtext style='width:170px;' id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 />
			</td>
			
			<td>Lokasi Pemesan</td> 
			<td>:</td>
			<td><select style=\"width:175px;\" onchange=getrekening() id=lokasipemesan>" . $optorgx . "</select></td>
		</tr>
		<tr>
			<td>Type Pembayaran</td> 
			<td>:</td>
			<td><select style=\"width:175px;\" onchange=getrekening() id=tipebayar>" . $opttipe . "</select></td>
			
			<td>Nomor Rekening</td> 
			<td>:</td>
			<td><select style=\"width:175px;\" onchange=getatasnama() id=rekening>" . $optbank . "</select>
			</td>
		</tr> 
		<tr>
			<td></td> 
			<td></td>
			<td></td>
			
			<td>Atas Nama (Penerima)</td> 
			<td>:</td>
			<td><input id=atasnama class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style='width:170px;'></td>
		</tr> 
		<tr>
			<td valign=top>Tujuan</td> 
			<td valign=top>:</td>
			<td colspan=4><textarea rows='4' cols='50' maxlength='767' id='tujuan' type='text' nkeypress='return_tanpa_kutip(event);' style='width:461px;'></textarea>
			</td>
		</tr> 
		<tr>
			<td colspan=2></td>
			<td>
				<button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
			<input type=hidden id=method value='insert'>
	</tr>
	</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=detail style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";
echo close_body();
?>
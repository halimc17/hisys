<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
	<script language=javascript1.2 src='js/master_barang.js?v=<?= time(); ?>'></script>
	<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
include('master_mainMenu.php');

$_SESSION['thargasatuan']=array();
if ($_SESSION['language'] == 'EN') {
    $zz = 'kelompok1 as kelompok';
} else {
    $zz = 'kelompok';
}

$jnsapp = "MB";

//pengambilan kelompok barang dari table kelompok barang
$str = "select kode," . $zz . " from " . $dbname . ".log_5klbarang where status='1' order by kode asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optkelompok = "<option value=''></option>";
//create option search
$optsearch = "<option value=All>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res->fetch()) {
    $optkelompok.="<option value='" . $bar->kode . "'>".$bar->kode." - " . $bar->kelompok . " </option>";
    $optsearch.="<option value='" . $bar->kode . "'>".$bar->kode . " - " . $bar->kelompok . "</option>";
}

//pengambilan sub kelompok barang dari table sub kelompok barang
$str = "select * from " . $dbname . ".log_5subklbarang where status='1' order by kode asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsubkelompok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optsubkelompok2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res->fetch()) {
    $optsubkelompok.="<option value='" . $bar->kode . "'>" . $bar->namasubkelompok . "</option>";
    $optsubkelompok2.="<option value='" . $bar->kode . "'>" . $bar->namasubkelompok . "</option>";
}

//pengambilan satuan dari table setup_satuan
$str = "select distinct satuan from " . $dbname . ".setup_satuan order by satuan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsatuan = '';
while ($bar = $res->fetch()) {
    $optsatuan.="<option value='" . $bar->satuan . "'>" . $bar->satuan . "</option>";
}
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optjenis.="<option value='slow'>Slow Moving</option>";
$optjenis.="<option value='fast'>Fast Moving</option>";
$optjenis.="<option value='non'>Non Moving</option>";

$opthargasatuan.="<option value=''>Global</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi) = '4' order by induk, tipe";
$res=fetchdata($str);
foreach($res as $val){
	$d=getNamaOrg($val['kodeorganisasi'],'induk');
	if($d!=$n){			
		$opthargasatuan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$opthargasatuan.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$opthargasatuan.="</optgroup>";
	}
}

#= Ambil kategori
$optKategori = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>"; 
$query = selectQuery($dbname,"log_5kategoribarang","*");
$result = fetchData($query);

foreach($result as $val) {
	$optKategori .= "<option value='".$val['id']."'>".$val['jenis']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('log_5masterbarang').'</span><br>');
echo"<fieldset style='float:left'>
	<legend>".$_SESSION['lang']['form']."</legend>
    <table border='0' cellspacing='0'>
        <tr>
            <td class='bintang'>".str_replace("."," ",$_SESSION['lang']['materialgroupcode'])."</td>
            <td>:</td>
			<td  colspan=4>
				<select style='width:300px' id='kelompokbarang' onchange=getSubKlBarang()>".$optkelompok."</select>
				<img id='kelompokbarang' onclick=z.elSearch('kelompokbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
        </tr>
        <tr>
            <td class='bintang'>".$_SESSION['lang']['kodesubkelompokbarang']."</td>
            <td>:</td>
			<td colspan=4>
				<select  style='width:300px' id='subkelompokbarang' onchange=getMaterialNumber() disabled='true'>
					".$optsubkelompok."</select>
			</td>
        </tr>
        <tr>
            <td class='bintang'>".$_SESSION['lang']['kodebarang']."</td>
			<td>:</td>
			<td colspan=4>
				<input style='width:95px' type=text  class=myinputtext  id='kodebarang' disabled size=10>
			</td>
        </tr>
        <tr>
            <td class='bintang'>".$_SESSION['lang']['materialname']."</td>
            <td>:</td>
			<td colspan=4>
				<input type='text'  class=myinputtext id='namabarang' size=49 maxlength=120 onkeypress='return tanpa_kutip(event)'>
			</td>
        </tr>
		<tr>
            <td class='bintang'>".$_SESSION['lang']['jenis']."</td>
            <td>:</td>
				<td>
					<select  style='width:100px' id='jenis'>".$optjenis."</select>
			</td>
			<td>".$_SESSION['lang']['inisial']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:100px' class=myinputtext id='inisial'  maxlength=10 onkeypress='return tanpa_kutip(event)'>
			</td>
        </tr>
        <tr>
            <td class='bintang'>".$_SESSION['lang']['satuan']."</td>
			<td>:</td>
			<td>
				<select  style='width:100px' id='satuan'>".$optsatuan."</select>
				<img id='satuan' onclick=z.elSearch('satuan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
  
			<td>".$_SESSION['lang']['konversi']."</td>
			<td>:</td>
			<td>
				<select  style='width:105px'  id='konversi'><option value=1>Yes</option><option value=0>No</option></select>
			</td>
        </tr>
		<tr>
            <td>".$_SESSION['lang']['ongkoskirim']."</td>
			<td>:</td>
			<td>
			<select style='width:105px' id='ongkir'>
				<option value='0'>Tidak Ada</option>
				<option value='1'>Ada - Tidak Masuk Harga Rata</option>
				<option value='2'>Ada - Masuk Harga Rata</option>
			</select>
		</td>
			<!--<td><input type=checkbox id=ongkir value=1></td>-->

			<td>".$_SESSION['lang']['status']."</td>
			<td>:</td>
			<td>
				<select style='width:105px' id='statusbarang'>
					<option value='0'>Aktif</option>
					<option value='1'>Non-Aktif</option>
				</select>
			</td>
		</tr>
		<tr>
            <td class='bintang'>".$_SESSION['lang']['hargasatuan']."</td>
			<td>:</td>
			<td colspan=4>
				<select id='hargasatuan' style='width:300px' onchange='chooseTarget(this.value)'>".$opthargasatuan."</select>
			</td>

		</tr>
		<tr>
			<td class='bintang'>Kategori Barang</td>
			<td>:</td>
			<td colspan=4><select style='width:300px' id=kategoriBarang>" . $optKategori . "</select></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=4>
				<div id='listhargasatuan'></div>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['kodevhc'] . "</td>
			<td>:</td>
			<td>
				<select id='kodevhc' style='width:100px'>
					<option value='0'>Tidak</option>
					<option value='1'>Wajib Terisi</option>
				</select>
			</td>
		</tr>
        <tr style=display:none>
            <td>".$_SESSION['lang']['minstok']."</td>
            <td>:</td>
			<td>
				<input style='width:95px' type='text'  class=myinputtextnumber id='minstok' value=0 size=4 maxlength=4 onkeypress='return angka_doang(event)'></td>
        </tr>
        <tr style=display:none>
            <td>".$_SESSION['lang']['nokartubin']."</td>
			<td>:</td>
			<td><input style='width:95px' type='text'  class=myinputtext id='nokartu' size=10 maxlength=10 onkeypress='return tanpa_kutip(event)'></td>
        </tr>
		<tbody id='trapproval'>";

		## APPROVAL ##
		$countApp = getCountApproval($jnsapp);
		for($i=1;$i<=$countApp;$i++){
			$optApp="";
			$arrlistapp = listApprove($i,$jnsapp);
			foreach($arrlistapp as $key=>$val){
				$optApp.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
			}
			echo"<tr>
				<td class='bintang'>".$_SESSION['lang']['persetujuan']." ".$i."</td>
				<td>:</td>
				<td colspan=4>
					<select style='width:100px' id='persetujuan".$i."'>".$optApp."</select>
				</td>
			</tr>";
		}
		
    echo"</tbody><input type=hidden value='insert' id=method>
    <tr><td><td><td  colspan=4>
    <button class=mybutton onclick=simpanBarangBaru()>".$_SESSION['lang']['save']."</button>
    <button class=mybutton onclick=cancelBarang()>".$_SESSION['lang']['cancel']."</button>
	</td></td></td></tr></table>
	</fieldset>";
CLOSE_BOX();
OPEN_BOX();

// echo "<fieldset>
// <img src='images/pdf.jpg' title='PDF Format' style='width:20px;height:20px;cursor:pointer' onclick=\"masterbarangPDF(event)\">&nbsp;
// <img src='images/printer.png' title='Print Page' style='width:20px;height:20px;cursor:pointer' onclick='javascript:print()'>
// <fieldset style='width:98%;background-color:#A9D4F4'>
// <legend><b>" . $_SESSION['lang']['find'] . "</b></legend>

echo"
	<fieldset style='float:left;'>
	<legend><b>" . $_SESSION['lang']['find'] . "</b></legend>
	<table border=0>
	<tr>
		<td>" . $_SESSION['lang']['kodebarang'] . "</td>
		<td>:</td>
		<td><input type=text id=txtcarikode class=myinputtext style=width:100px onkeypress=\"key=getKey(event);if(key==13){cariBarang()};return tanpa_kutip(event);\" maxlength=30></td>
		
		<td style='padding-left:20px'>" . $_SESSION['lang']['namabarang'] . "</td>
		<td>:</td>
		<td><input type=text id=txtcari class=myinputtext size=40 onkeypress=\"key=getKey(event);if(key==13){cariBarang()};return tanpa_kutip(event);\" maxlength=30></td>
		
		<td style='padding-left:20px'>" . str_replace("."," ",$_SESSION['lang']['materialgroupcode']) . "</td>
		<td>:</td>
		<td><select id=optcari onchange=getSubKlBarang2()>" . $optsearch . "</select>
			<img id='optcari' onclick=z.elSearch('optcari',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
		<td><button class=mybutton onclick=cariBarang()>" . $_SESSION['lang']['find'] . "</button></td>
	</tr>
	
	<tr>
		<td>" . $_SESSION['lang']['jenis'] . "</td>
		<td>:</td>
		<td><select id=jenissch  style=width:100px onchange=cariBarang()>" . $optjenis . "</select></td>
		
		<td style='padding-left:20px'>" . $_SESSION['lang']['persetujuan'] . "</td>
		<td>:</td>
		<td><select id=optcaripersetujuan style=width:251px>
			<option value='all'>Seluruhnya</option>
			<option value='1'>Belum disetujui</option>
			<option value='0'>Sudah disetujui</option>
		</select>
		</td>

		
		<td style='padding-left:20px'>" . $_SESSION['lang']['kodesubkelompokbarang'] . "</td>
		<td>:</td>
		<td>
			<select  style='width:300px' id='subkelompokbarangcr' onchange=cariBarang() disabled='true'>".$optsubkelompok2."</select>
			<img id='subkelompokbarangcr' onclick=z.elSearch('subkelompokbarangcr',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
		
		<td><button class=mybutton onclick=cancelsearch()>" . $_SESSION['lang']['cancel'] . "</button></td>
	</tr>
</table>
</fieldset>

<div style='width:100%;overflow:auto;height:60vh;'>
<b id=caption></b>
      <table cellspacing=1 border=0 class=sortable width=100%>
      <thead>
	  <tr class=rowheader>
	  <th align=center>No</th>
	  <th align=center colspan=2>" .$_SESSION['lang']['materialgroupcode']. "</th>
	  <th align=center colspan=2>" .$_SESSION['lang']['kodesubkelompokbarang'] . "</th>
	  <th align=center>" . $_SESSION['lang']['materialcode'] . "</th>
	  <th align=center>" . $_SESSION['lang']['materialname'] . "</th>
	  <th align=center>" . $_SESSION['lang']['satuan'] . "</th>
	  <th align=center>" . $_SESSION['lang']['jenis'] . "</th>
	  <th align=center style=display:none>" . $_SESSION['lang']['keterangan'] . "</th>
	  <th align=center style=display:none>" . str_replace(" ", "<br>", $_SESSION['lang']['minstok']) . "</th>
	  <th align=center style=display:none>" . str_replace(" ", "<br>", $_SESSION['lang']['nokartubin']) . "</th>
	  <th align=center>" . $_SESSION['lang']['konversi'] . "</th>
	  <th align=center>" . $_SESSION['lang']['inisial'] . "</th>
	  <th align=center>" . $_SESSION['lang']['status'] . "</th>
	  <th align=center>" . $_SESSION['lang']['createby'] . "</th>
	  <th align=center>" . $_SESSION['lang']['updateby'] . "</th>
	  ";	  
		// for($i=1;$i<=$countApp;$i++){
			// echo"<th align=center>".$_SESSION['lang']['persetujuan']." ".$i."</th>";
		// }
	echo"<th align=center>".$_SESSION['lang']['persetujuan']."</th>";
	echo "<th align=center>QR Code</th>
	  <th align=center>Kategori Barang</th>	  
	  <th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
	  <th align=center>Detail<br>" . $_SESSION['lang']['photo'] . "</th>
	  <th align=center>Action</th>
	  </tr>
	  </thead>
	  <tbody id=container>
	  </tbody>
	  <tfoot>
	  </tfoot>
	  </table>
</div>";
CLOSE_BOX();
echo close_body();
?>
<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zSearch.js'></script>
<script language=javascript1.2 src='js/bi_5upload.js'></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<?
include('master_mainMenu.php');
OPEN_BOX('','');

$frm[0]='';
$frm[1]='';
$frm[2]='';


//===============BEGIN PETA DASAR===============//
//Get Tipe Peta
$optTipePeta = "";
$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '0' order by keterangan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optTipePeta.="<option value='".$bar['id_tipepeta']."'>".$bar['keterangan']."</option>";
}

$optTipePeta2 = "";
$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '1' order by keterangan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optTipePeta2.="<option value='".$bar['id_tipepeta']."'>".$bar['keterangan']."</option>";
}

//Get Provinsi
$optProvinsi = "";
$str = "select * from ".$dbname.".provinsi order by provinsi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optProvinsi .= "<option value='".$bar['id']."'>".$bar['provinsi']."</option>";
}

//Get Provinsi
$optProvinsi2 = "";
$str = "select distinct(t1.namapeta) as id, t2.provinsi as provinsi from ".$dbname.".bi_map_basic t1
		LEFT JOIN ".$dbname.".provinsi t2 ON t1.namapeta = t2.id 
		where t1.tipepeta = 'MAP001' ORDER BY t2.provinsi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optProvinsi2 .= "<option value='".$bar['id']."'>".$bar['provinsi']."</option>";
}

$frm[0] .= "<div id='entrydata1'><fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			<td>:</td>
			<td>
				<input id=id class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipepeta']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id=tipepeta style='width:150px;' onchange='getNamaPeta()'>".$optTipePeta."</select>
				<img id=tipepeta_find onclick=\"z.elSearch('tipepeta',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>Nama Peta</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=namapeta class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >
				
				<select id=provinsi style='width:150px;display:none'>".$optProvinsi."</select>
				<img id=provinsi_find onclick=\"z.elSearch('provinsi',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;display:none'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['filesvg']."</td>
			<td></td>
			<td><input name=upload type=file id=upload size=25 class=mybutton></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=method value='insert'>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

$frm[0] .= "<fieldset style='width:200px;'>
	<legend>Note</legend>
	<div id='sus_dt_list'></div>
</fieldset></div>";

$frm[0] .= "<div style=clear:both;>&nbsp;</div>";
$frm[0] .= "<div id='frm1'><fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
    <script>loadalldata(0)</script>
	<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
		<tr align=center>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['id']."</td>
			<td>".$_SESSION['lang']['tipepeta']."</td>
			<td>Nama Peta</td>
			<td colspan=3>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='container'>
		</tbody>
		<tfoot id=footData> 
		</tfoot>
	</table>
</fieldset></div>";

//=======PETA PT=======
//Get All PT
$optPT = $optDivisi = $optKebun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".organisasi where tipe = 'PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optPT .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
$frm[1] .= "<div id='entrydata1'><fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			<td>:</td>
			<td>
				<input id=id2 class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>Provinsi</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id=provinsi2 style='width:150px;'>".$optProvinsi2."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id='kodept2' onchange=\"getkebun2();\">".$optPT."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id='kebun2'>".$optKebun."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipepeta']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id=tipepeta2 style='width:150px;'>".$optTipePeta2."</select>
				<img id=tipepeta_find2 onclick=\"z.elSearch('tipepeta2',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>Nama Peta</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<input id=namapeta2 class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\" >
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['filesvg']."</td>
			<td></td>
			<td><input name=upload2 type=file id=upload2 size=25 class=mybutton></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=method2 value='insert'>
				<button class=mybutton onclick=simpan2()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal2()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

$frm[1] .= "<fieldset style='width:200px;'>
	<legend>Note</legend>
	<div id='sus_dt_list'></div>
</fieldset></div>";

$frm[1] .= "<div style=clear:both;>&nbsp;</div>";
$frm[1] .= "<div id='frm2'><fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
    <div id='showlist2'>
	<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
		<tr align=center>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['id']."</td>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>".$_SESSION['lang']['provinsi']."</td>
			<td>".$_SESSION['lang']['tipepeta']."</td>
			<td>Nama Peta</td>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td colspan=3>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='container2'>
		</tbody>
		<tfoot id=footData2> 
		</tfoot>
	</table>
	</div>
</fieldset></div>";

//=======PETA Lainnya=======
//Get Periode Akutansi
//Get existing period
$str="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optper='';
while($bar=$res->fetch()){
    $optper.="<option value='".$bar['periode']."'>".substr($bar['periode'],5,2)."-".substr($bar['periode'],0,4)."</option>";
}

//Get Tipe Dokumen
$optTipeDokumen3 = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select id_tipedok, nama_tipe from ".$dbname.".bi_5tipedok order by nama_tipe asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTipeDokumen3 .= "<option value='".$bar['id_tipedok']."'>".$bar['nama_tipe']."</option>";
}

$_SESSION['nodok'] = array();
$frm[2] .= "<div id='entrydata1'><fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			<td>:</td>
			<td>
				<input id=id3 class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select id='periode3'>".$optper."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id='kodept3' onchange='getkebun3()'>".$optPT."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id=kebun3 onchange='getNoDok3()'>".$optKebun."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipedokumen']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id=tipedokumen3 onchange='getNoDok3()'>".$optTipeDokumen3."</select>
				<img id=tipedokumen_find3 onclick=\"z.elSearch('tipedokumen3',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kegiatan']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<select id=kegiatan3>".$optKebun."</select>
			</td>
		</tr>
		<tr id='trnodok' style='display:none'>
			<td colspan=2></td>
			<td style='padding-right:10px'>
				<table>
					<thead>
					<tr>
						<td>".$_SESSION['lang']['nodokumen']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
					</thead>
					<tbody id='newnodok'>
					</tbody>
					<tr>
						<td>
							<input id=nodok class=myinputtext disabled>
							<img id=tipedokumen_find3 onclick=\"searchnodok('Cari No. Dokumen','<div id=formPencariandata></div>',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
						</td>
						<td style='text-align:center'>
							<img title='Tambah' class=resicon onclick=\"addNoDok()\" src='images/plus.png'/>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['fitur']."</td>
			<td>:</td>
			<td>
				<input type='radio' name='fitur' value='Polygon' checked> Polygon &nbsp;&nbsp;&nbsp;
				<input type='radio' name='fitur' value='Polyline'> Polyline
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['warna']."</td>
			<td>:</td>
			<td>
				<input disabled type=text class=myinputtext id=kodefill name=kode onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\" />
				<img  class=resicon src=images/color_fill.png style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna(event)>     	  
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['filesvg']."</td>
			<td>:</td>
			<td>
				<input name=upload3 type=file id=upload3 size=25 class=mybutton>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>
			<td style='padding-right:10px'>
				<textarea id='keterangan3'></textarea>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=method3 value='insert'>
				<button class=mybutton onclick=simpan3()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal3()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

$frm[2] .= "<fieldset style='width:200px;'>
	<legend>Note</legend>
	<div id='sus_dt_list'></div>
</fieldset></div>";

$frm[2] .= "<div style=clear:both;>&nbsp;</div>";
$frm[2] .= "<div id='frm3'><fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
    <div id='showlist2'>
	<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
		<tr align=center>
			<td>".$_SESSION['lang']['nourut']."</td>
			<td>".$_SESSION['lang']['id']."</td>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>".$_SESSION['lang']['tipedokumen']."</td>
			<td>".$_SESSION['lang']['kegiatan']."</td>
			<td>".$_SESSION['lang']['fitur']."</td>
			<td>".$_SESSION['lang']['warna']."</td>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td colspan=3>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='container3'>
		</tbody>
		<tfoot id=footData3> 
		</tfoot>
	</table>
	</div>
</fieldset></div>";


//=======================================
$hfrm[0]="Peta Dasar";
$hfrm[1]="Peta PT";
$hfrm[2]="Peta Lainnya";
drawTab('FRM',$hfrm,$frm,150,'100%');
//=======================================	

CLOSE_BOX();
echo close_body();
?>
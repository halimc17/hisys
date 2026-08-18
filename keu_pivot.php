<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zPivot.php');
require_once('lib/zSelect2Lite.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_pivot').'</span><br>');
?>
<script language=javascript src='js/keu_pivot.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_laporanxx.js?v=2'></script>
<script language=javascript src='js/report_pivottable.js?v=<?php echo time(); ?>'></script>

<script type='text/javascript' src='DataTables/js/jquery.dataTables.min.js'></script>
<link rel='stylesheet' type='text/css' href='DataTables/css/jquery.dataTables.min.css'>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?
// <style>
	// /* styles for responsive pivot UI */
	// table.pvtUi {
		// table-layout: fixed;
		// width: 100%;
	// }
	
	// /*Horizontal*/
	// table.pvtUi > tbody > tr:first-child > td:first-child {
		// width: 200px;
	// }
	
	// /*Vertical*/
	// table.pvtUi > tbody > tr:first-child > td:nth-child(2) {
		// width: 210px;
	// }
	
	// .pvtTableRendererHolder {
		// max-height: 900px;
	// }

	// .pvtRendererArea > div {
		// overflow: auto;
	// }
// </style>

$str="select DISTINCT substr(tanggal,1,7) as prd from ".$dbname.".keu_jurnalht where substr(tanggal,1,7) not in ('0000-00') order by substr(tanggal,1,7) desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
}
$no=0;
$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($data as $thn => $vprd){
	$d=$thn;
	if($d!=$n){			
		$optprd.="<optgroup label='".$d."'>";
	}
	//$optprd.="<option value=".$thn." ".$n.">".$thn."</option>";			
	foreach($vprd as $prd){
		$optprd.="<option value=".$prd.">".$prd."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optprd.="</optgroup>";
	}
}

$optnamakaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
    $optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
    $optgudang=$optReg="<option value=''>".$_SESSION['lang']['all']."</option>";


    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
          where tipe='PT'
          order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."-".$bar->namaorganisasi."</option>";
    }
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
    
    $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
    $iUnit=$owlPDO->query("select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."' ");
    $iUnit->setFetchMode(PDO::FETCH_ASSOC);
    while($dUnit=  $iUnit->fetch())
    {
        $optUnit.="<option value='".$dUnit['kodeunit']."'>".$nmOrg[$dUnit['kodeunit']]."</option>";
    }
    $optgudang = $optUnit;
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']." - ".getNamaOrg($_SESSION['empl']['kodeorganisasi'])."</option>";
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
} else {
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']." - ".getNamaOrg($_SESSION['empl']['kodeorganisasi'])."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
}
    

$optKel="<option value=''>".$_SESSION['lang']['all']."</option>";
$iKel=$owlPDO->query("select distinct(kodekelompok) as kodekelompok,keterangan from ".$dbname.".keu_5kelompokjurnal");
$iKel->setFetchMode(PDO::FETCH_ASSOC);
while($dKel= $iKel->fetch()){
    $optKel.="<option value='".$dKel['kodekelompok']."'>".$dKel['kodekelompok']." - ".$dKel['keterangan']."</option>";
}
$arrtipe=array(
	'rekap'=>'Rekap Jurnal (Prd, Akun)',
	'rekapkeg'=>'Rekap Jurnal (Prd, Akun, Keg)',
	'rekapsupp'=>'Rekap Jurnal (Prd, Akun, Assign)',
	'jurnal'=>'Detail Jurnal'
);
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrtipe as $key => $val){
	$opttipe.="<option value=".$key.">".$val."</option>";			
}

$filter="
	<fieldset><legend>More Filter</legend>
	<table border=0>
		<thead></thead>
		<tbody>
		<tr>
			<td>Kolom</td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('s');>S</button></td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('i');>I</button></td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('k');>K</button></td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('c');>C</button></td>
			
			<td></td>
			<td>Kolom</td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('s1');>S</button></td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('i1');>I</button></td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('k1');>K</button></td>
			<td align=center><button class=mybutton style=width:30px onclick=selectall('c1');>C</button></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kegiatan']."</td>
			<td><input type=radio checked class=s[] name=kegiatan[] value=s onchange=getcustom(this.name,'kegiatan');></td>
			<td><input type=radio class=i[] name=kegiatan[] value=i onchange=getcustom(this.name,'kegiatan');></td>
			<td><input type=radio class=k[] name=kegiatan[] value=k onchange=getcustom(this.name,'kegiatan');></td>
			<td><input type=radio class=c[] name=kegiatan[] value=c onchange=getcustom(this.name,'kegiatan');></td>
			<td><input class=myinputtext name=ci[] id=kegiatan disabled style=display:none></td>
			
			<td>".$_SESSION['lang']['karyawan']."</td>
			<td><input type=radio checked class=s1[] name=karyawan[] value=s onchange=getcustom(this.name,'karyawan');></td>
			<td><input type=radio class=i1[] name=karyawan[] value=i onchange=getcustom(this.name,'karyawan');></td>
			<td><input type=radio class=k1[] name=karyawan[] value=k onchange=getcustom(this.name,'karyawan');></td>
			<td><input type=radio class=c1[] name=karyawan[] value=c onchange=getcustom(this.name,'karyawan');></td>
			<td><input class=myinputtext name=c1i[] id=karyawan disabled style=display:none></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['blok']."</td>
			<td><input type=radio checked class=s[] name=blok[] value=s onchange=getcustom(this.name,'blok');></td>
			<td><input type=radio class=i[] name=blok[] value=i onchange=getcustom(this.name,'blok');></td>
			<td><input type=radio class=k[] name=blok[] value=k onchange=getcustom(this.name,'blok');></td>
			<td><input type=radio class=c[] name=blok[] value=c onchange=getcustom(this.name,'blok');></td>
			<td><input class=myinputtext name=ci[] id=blok disabled style=display:none></td>
			
			<td>".$_SESSION['lang']['noreferensi']."</td>
			<td><input type=radio checked class=s1[] name=noreff[] value=s onchange=getcustom(this.name,'noreff');></td>
			<td><input type=radio class=i1[] name=noreff[] value=i onchange=getcustom(this.name,'noreff');></td>
			<td><input type=radio class=k1[] name=noreff[] value=k onchange=getcustom(this.name,'noreff');></td>
			<td><input type=radio class=c1[] name=noreff[] value=c onchange=getcustom(this.name,'noreff');></td>
			<td><input class=myinputtext name=c1i[] id=noreff disabled style=display:none></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namabarang']."</td>
			<td><input type=radio checked class=s[] name=barang[] value=s onchange=getcustom(this.name,'barang');></td>
			<td><input type=radio class=i[] name=barang[] value=i onchange=getcustom(this.name,'barang');></td>
			<td><input type=radio class=k[] name=barang[] value=k onchange=getcustom(this.name,'barang');></td>
			<td><input type=radio class=c[] name=barang[] value=c onchange=getcustom(this.name,'barang');></td>
			<td><input class=myinputtext name=ci[] id=barang disabled style=display:none></td>
			
			<td>".$_SESSION['lang']['nodok']."</td>
			<td><input type=radio checked class=s1[] name=nodok[] value=s onchange=getcustom(this.name,'nodok');></td>
			<td><input type=radio class=i1[] name=nodok[] value=i onchange=getcustom(this.name,'nodok');></td>
			<td><input type=radio class=k1[] name=nodok[] value=k onchange=getcustom(this.name,'nodok');></td>
			<td><input type=radio class=c1[] name=nodok[] value=c onchange=getcustom(this.name,'nodok');></td>
			<td><input class=myinputtext name=c1i[] id=nodok disabled style=display:none></td>
		</tr><tr>
			<td>".$_SESSION['lang']['supplier']."</td>
			<td><input type=radio checked class=s[] name=supplier[] value=s onchange=getcustom(this.name,'supplier');></td>
			<td><input type=radio class=i[] name=supplier[] value=i onchange=getcustom(this.name,'supplier');></td>
			<td><input type=radio class=k[] name=supplier[] value=k onchange=getcustom(this.name,'supplier');></td>
			<td><input type=radio class=c[] name=supplier[] value=c onchange=getcustom(this.name,'supplier');></td>
			<td><input class=myinputtext name=ci[] id=supplier disabled style=display:none></td>
			
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td><input type=radio checked class=s1[] name=keterangan[] value=s onchange=getcustom(this.name,'keterangan');></td>
			<td><input type=radio class=i1[] name=keterangan[] value=i onchange=getcustom(this.name,'keterangan');></td>
			<td><input type=radio class=k1[] name=keterangan[] value=k onchange=getcustom(this.name,'keterangan');></td>
			<td><input type=radio class=c1[] name=keterangan[] value=c onchange=getcustom(this.name,'keterangan');></td>
			<td><input class=myinputtext name=c1i[] id=keterangan disabled style=display:none></td>
		</tr>
		</tbody>
	</table>
	</fieldset>
";
$info="<b>S</b> : Seluruhnya, <b>I</b> : Kolom != Kosong, <b>K</b> : Kolom = Kosong, <b>C</b> : Custom";

$optakun="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".keu_5akun";
$res = fetchData($str);
foreach($res as $val){
	if(strlen($val['noakun'])==3){
		$d=$val['noakun'];
		if($d!=$n){			
			$optakun.="<optgroup label='".$d." - ".$val['namaakun']."'>";
		}
	}
	if(strlen($val['noakun'])==7){		
		$optakun.="<option value=".$val['noakun'].">".$val['noakun']." - ".$val['namaakun']."</option>";			
	}
	
	$n=$d;
	if($d!=$n){			
		$optakun.="</optgroup>";
	}
}


$path   = $_SERVER['SCRIPT_NAME'];
$path   = explode('/',$path);
$rowfile= count($path)-1;
$file   = $path[$rowfile];
$file   = str_replace(".php","",$file);
$idmenu = makeOption($dbname,'menu','action,id');


$optlap="<option value=''>&nbsp;</option>";
$str = "select a.*, b.karyawanid, b.label, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' order by b.karyawanid ,a.id asc";
$res = fetchdata($str);
$fav = count($res);
foreach ($res as $bar){
	
	$d=$bar['karyawanid'];
	if($d!=$n){			
		$optlap.="<optgroup label='".getNamaKaryawan($d)."'>";
	}
	$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	$n=$d;
	if($d!=$n){			
		$optlap.="</optgroup>";
	}
}

if($fav==0){
	$st="style=display:none";
}

echo"<table border=0 id=tableheader><td style=vertical-align:top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr ".$st.">
					<td>Tampilkan dari</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=fromfavorit onchange=getfromfav(); style=\"width:203px;\">".$optlap."</select></td>
                </tr>
				<tr>
					<td hidden id=nik></td>
					<td>".$_SESSION['lang']['pt']."</td>
					<td>:</td>
					<td colspan=3><select class='select2' id=pt style='width:203px;'  onchange=getkaryawan();>".$optpt."</select></td>
					
					<td rowspan=6 valign=top style=display:none id=morefilter>".$filter."</td>
                </tr>
				<tr>
					<td>".$_SESSION['lang']['regional']."</td>
					<td>:</td>
					<td colspan=3><select class='select2' id=regional style='width:203px;' onchange=getUnit()>".$optReg."</select> </td>
                </tr>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td colspan=3><select class='select2' id=gudang style='width:203px;'>".$optgudang."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td> 
					<td>:</td>
					<td><select class='select2' id=periode onchange=getper2('periode','periode2','1'); style=\"width:90px;\">".$optprd."</select></td>
					<td>s/d</td>
					<td><select class='select2' id=periode2 onchange=getper2('periode','periode2','2'); style=\"width:90px;\">".$optprd."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['noakun'] . "</td> 
					<td>:</td>
					<td><select class='select2' id=noakun onchange=getper2('noakun','noakun2','1'); style=\"width:90px;\">".$optakun."</select></td>
					<td>s/d</td>
					<td><select class='select2' id=noakun2 onchange=getper2('noakun','noakun2','2'); style=\"width:90px;\">".$optakun."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['jenis'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=tipe onchange=sumber(this.value); style=\"width:203px;\">".$opttipe."</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=2>
						<button onclick=pivot(); class=mybutton>Pivot</button>
                    </td>
                    <td onclick=morefilter(); style=cursor:pointer></td>
                    <td id=moreinfo style=display:none>".$info."</td>
                </tr>
            </table>
</fieldset>";
echo"</td><td style=vertical-align:top>";
echo"<div id=info></div>";
echo"</td></table>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"dt-button buttons-print\" id=showhead>Show</button>
			<button onclick=\"exportTableToExcel('pvtTable')\" class=\"dt-button buttons-print\" >Excel</button>
			<button onclick='ExportPdf()' class=\"dt-button buttons-print\">PDF</button>
			<button id=download onclick='pdftosave()' class=\"dt-button buttons-print\">Send</button>
			<div hidden id=namamenu>".getMenu('keu_pivot','path')."</div>
			<button onclick=clickpopup() class=\"dt-button buttons-print\" id=totaldata></button>
			<button id=formfav onclick=\"formfav()\" class=\"dt-button buttons-print\">Add Favorit</button>
		</td><td>			
			<fieldset >
					<label>Show Favorit</label>
					<select id=optfavorit style=\"width:150px;\"></select>
			</fieldset>
		</td></tr>
	</table>
	</div>";
	
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>
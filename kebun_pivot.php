<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zPivot.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_pivot').'</span><br>');
?>
<script language=javascript src='js/kebun_pivot.js?v=<?php echo time(); ?>'></script>
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
// /*
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
// */
// </style>

$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	if($tipe[$key]=='KEBUN'){		
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
		$n=$d;
		if($d!=$n){			
			$optorg.="</optgroup>";
		}
	}
}

$str="select DISTINCT (substr(tanggal,1,7)) as prd from ".$dbname.".pabrik_timbangan order by prd desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
}
$no=0;
foreach($data as $thn => $vprd){
	$optprd.="<option value=".$thn." ".$n.">".$thn."</option>";			
	foreach($vprd as $prd){
		$optprd.="<option value=".$prd.">".$prd."</option>";			
	}
}
$arrtipe=array(
	'aresta'    =>'Areal Statement',
	'prd'       =>'Produksi TBS (Tanggal SPB)',
	'prdpnn'    =>'Produksi TBS (Tanggal Panen)',
	'prdbgt'    =>'Produksi vs Budget (Harian)',
	'prdbgtbln' =>'Produksi vs Budget (Bulanan)',
	'tmb'       =>'Pabrik Timbangan',
	'bkm'       =>'Buku Kegiatan Mandor (BKM) Rawat',
	'pnn'       =>'Buku Kegiatan Mandor (BKM) Panen',
	'upahpanen' =>'Upah Karyawan Panen',
	'detpayroll'=>'Detail Absensi SDM (Aktual)',
	'atbs'      =>'Pemby Angkut TBS',
	'bapp'      =>'BAPP Kontraktor',
	'fee'       =>'Biaya Admin Panen',
	'byy'       =>'Biaya Panen dan Pemel',
	'mat'       =>'Pengeluaran / Pemakaian Material'
);
foreach($arrtipe as $key => $val){
	$opttipe.="<option value=".$key.">".$val."</option>";			
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
					<td colspan=3><select class='select2' id=fromfavorit onchange=getfromfav(); style=\"width:200px;\">".$optlap."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=kodeorg style=\"width:200px;\">".$optorg."</select>
					</td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=periode style=\"width:200px;\">".$optprd."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['jenis'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=tipe onchange=sumber(this.value); style=\"width:200px;\">".$opttipe."</select></td>
                </tr>
				<tr id=tanggal style=display:none>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' readonly></td><td>s/d</td><td>
						<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' readonly>
					</td>
				</tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=8>
                    <button onclick=pivot(); id=getpivot class=mybutton>Pivot</button>
					<button hidden onclick=\"data()\" class=mybutton>Data</button>
                    </td>
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
				<button id=download onclick=\"pdftosave()\" class=\"dt-button buttons-print\">Send</button>
				<div hidden id=namamenu>".getMenu('kebun_pivot','path')."</div>
				<button id=hidetotal onclick=\"hidetotal('r',1)\" class=\"dt-button buttons-print\">Show Total Row</button>
				<button id=hidetotalcol onclick=\"hidetotal('c',1)\" class=\"dt-button buttons-print\">Hide Total Col</button>
				<button id=formfav onclick=\"formfav()\" class=\"dt-button buttons-print\">Add Favorit</button>
			
				<input hidden id=temphidetotal value='0'>
				<input hidden id=temphidetotalcol value='1'>
		</td><td>			
			<fieldset >
					<label>Show Favorit</label>
					<select id=optfavorit style=\"width:150px;\"></select>
			</fieldset>
		</td></tr>
	</table>
	</div>";
echo"<div id='output' style=min-height:400px></div>";
// echo"<div id='output2' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>
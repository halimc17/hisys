<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/sdm_uploadabsensi.js></script>
<script>function submitFile(){
    if(confirm('Are you sure..?')){
    document.getElementById('frm').submit();
    }
}

function getform()
{
	// help_slave_detailbantuan.php?index=6&modul=Pengadaan
	ev='event';
    param = 'method=detailcomment';
    title="Data Detail";
	showDialog1(title,"<iframe frameborder=0 style='width:895px;height:395px'"+
    " src='help_slave_detailbantuan.php?index=616&modul=SDM'></iframe>",'900','400',ev);	
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
</script>
<?

include('master_mainMenu.php');
$frm[0] = '';
$frm[1] = '';

$arr="##listTransaksi##pilUn_1##unitId##method";
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['absensi']." (UPLOAD)").'</span>');
// $frm[0].="<fieldset>
	// <legend>Form</legend>
		// <div id=uForm>
        // <span id=sample>
		// <table>
			// <tr>
				// <td>Catatan :</td>
			// </tr>
			// <tr>
				// <td></td>
				// <td>
					// 1. Tools ini digunakan untuk meng-upload data absensi yang bersumber dari system lain, contoh : Fingerprint.
				// </td>
			// </tr>
			// <tr>
				// <td></td>
				// <td>
					// 2. Template file upload dapat di download <a href=tool_slave_getExample.php?form=ABSENSI target=frame>disini.</a>
				// </td>
			// </tr>
			// <tr>
				// <td></td>
				// <td>
					// 3. Detail penjelasan untuk upload data dapat dilihat 
					// <span style=cursor:pointer onclick=getform()><font color=blue><u>disini.</u></font></span>
				// </td>
			// </tr>
			// <tr>
				// <td></td>
				// <td>
					// 4. File type hanya support CSV.
				// </td>
			// </tr>
		// </table>
		// </span>
		// <br>
		
		// <form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>
			// <input type=hidden name=jenisdata id=jenisdata value='ABSENSI'>
			// <input type=hidden name=MAX_FILE_SIZE value=1024000>
			// File : <input name=filex type=file id=filex size=25 class=mybutton>
			// Field separated by : 
			// <select name=pemisah>
				// <option value=','>, (comma)</option>
				// <option value=';'>; (semicolon)</option>
				// <option value=':'>: (two dots)</option>
				// <option value='/'>/ (devider)</option>
			// </select>
			// <input type=button class=mybutton  value=".$_SESSION['lang']['save']." title='Submit this File' onclick=submitFile()>
		// </form>
		
		// <iframe frameborder=0 width=800px height=200px name=frame></iframe>
	// </div>
// </fieldset>";

//GET KODE ORGANISASI
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe IN ('KEBUN','PABRIK','AFDELING')	ORDER BY kodeorganisasi ASC";
}else{
	$idOrg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe NOT LIKE '%GUDANG%'  and  (kodeorganisasi='".$idOrg."' or induk='".$_SESSION['empl']['lokasitugas']."') ORDER BY kodeorganisasi ASC";
	if(strlen($_SESSION['empl']['subbagian']) == 6) 
	{
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$idOrg."' or induk='".$_SESSION['empl']['lokasitugas']."') and kodeorganisasi like '" . $_SESSION['empl']['subbagian'] . "%' and tipe NOT LIKE '%GUDANG%' ORDER BY `kodeorganisasi` ASC";
	}
}

// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optOrg = "";
while ($bar = $res->fetch())
{
	$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

//GET KODE ORGANISASI Fingerprint
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe IN ('KEBUN','PABRIK','AFDELING') and kodeorganisasi in (select kodeorg from ".$dbname.".sdm_5ipfinger)	ORDER BY kodeorganisasi ASC";
}else{
	$idOrg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe NOT LIKE '%GUDANG%'  and  (kodeorganisasi='".$idOrg."' or induk='".$_SESSION['empl']['lokasitugas']."')  and kodeorganisasi in (select kodeorg from ".$dbname.".sdm_5ipfinger) ORDER BY kodeorganisasi ASC";
	if(strlen($_SESSION['empl']['subbagian']) == 6) 
	{
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$idOrg."' or induk='".$_SESSION['empl']['lokasitugas']."') and kodeorganisasi like '" . $_SESSION['empl']['subbagian'] . "%' and tipe NOT LIKE '%GUDANG%'  and kodeorganisasi in (select kodeorg from ".$dbname.".sdm_5ipfinger) ORDER BY `kodeorganisasi` ASC";
	}
}

// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optOrgF = "";
while ($bar = $res->fetch())
{
	$optOrgF.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

//GET KODE ABSENSI
$str="select kodeabsen,keterangan from ".$dbname.".sdm_5absensi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optAbsen = "";
while ($bar = $res->fetch())
{
	$optAbsen.="<option value=".$bar['kodeabsen']." ".($bar['kodeabsen']=='H'?"selected":"").">".$bar['keterangan']."</option>";
}

$frm[0] = "<fieldset><table cellspacing='1' border='0'>
            <tr>
                <td>".$_SESSION['lang']['kodeorg']."</td>
                <td>:</td>
                <td>
                    <select id='kdOrg' style='width:150px;' onchange='getkaryawan()'>
						<option value=''>".$_SESSION['lang']['pilihdata']."</option>
						".$optOrg."
					</select>
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td>
                    <input type='text' class='myinputtext' id='tglAbsen' name='tglAbsen' onmousemove='setCalendar(this.id)' onkeypress='return false;' value='".date('d-m-Y')."'  maxlength='10' style='width:145px;' onchange='getkaryawan()' />
                </td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['namakaryawan']."</td>
                <td>:</td>
                <td>
                    <select style='width:250px' id=krywnId name=krywnId>
						<option value=''>".$_SESSION['lang']['pilihdata']."</option>
					</select>
					<img id='krywnId' onclick=z.elSearch('krywnId',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
                </td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['absensi']."</td>
                <td>:</td>
                <td>
					<select style='width:150px;' id=absniId name=absniId>".$optAbsen."</select>
				</td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['nomor']." BA</td>
                <td>:</td>
                <td>
					<input type='text' class='myinputtext' onkeypress='return tanpa_kutip(event);' style='width:145px;' id=noba name=noba />
				</td>
            </tr>
			
			<tr>
                <td colspan=2></td>
                <td>
					<input type='hidden' class='myinputtext' id='method' value='insert'>
					<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				</td>
            </tr>
        </table></fieldset>
		
		<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' cellpadding='3' border='0'>
				<thead>
					<tr class=rowheader>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['kodeorg']."</td>
						<td>".$_SESSION['lang']['nomor']." BA</td>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>".$_SESSION['lang']['namakaryawan']."</td>
						<td>".$_SESSION['lang']['absensi']."</td>
						<td colspan='1' style='text-align:center;'>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>
				<tbody id='container'>
				<script>loadData()</script>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</fieldset>
		";
		
$frm[1] = "<fieldset><table cellspacing='1' border='0'>
            <tr>
                <td>".$_SESSION['lang']['kodeorg']."</td>
                <td>:</td>
                <td>
                    <select id='kdOrg2''>
						<option value=''>".$_SESSION['lang']['pilihdata']."</option>
						".$optOrgF."
					</select>
                </td>
				<td style='padding-left:20px;vertical-align:top' rowspan=3>
					<fieldset>
					<legend>".$_SESSION['lang']['info']."</legend>
					<table cellspacing='1' border='0'>
						<tr>
							<td>=> Data karyawan yang ditampilkan hanya karyawan yang NIK di Fingerprint <b>=</b> NIK di <b>OWL</b>
							<br>=> List Kode Organisasi hanya ditampilkan yang ada di menu Setup IP Fingerprint
							</td>
						</td>
					</table>
					</fieldset>
				</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td>
                    <input type='text' class='myinputtext' id='tglAbsen2' onmousemove='setCalendar(this.id)' onkeypress='return false;' value='".date('d-m-Y')."'  maxlength='10' style='width:100px;' />
                </td>
            </tr>
			<tr>
                <td colspan=2></td>
                <td>
					<input type='hidden' class='myinputtext' id='method' value='insert'>
					<button class=mybutton onclick=preview2()>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=batal2()>".$_SESSION['lang']['cancel']."</button>
				</td>
            </tr>
        </table></fieldset>
		
		<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<div id='container2'></div>
		</fieldset>
		";

// $hfrm[0] = "Form Upload Absensi";
$hfrm[0] = "Form Input Manual";
$hfrm[1] = "Form Upload Device";
drawTab('FRM', $hfrm, $frm, 170, 1000);

CLOSE_BOX();
 
echo close_body();
?>
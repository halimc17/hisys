<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5mappingnikfp').'</span>','judul_header');
?>
<script language="javascript" src="js/sdm_5mappingnikfp.js?v=<?php echo time(); ?>"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
##GET UNIT

$arrUnit = getOrgDetail(2);
$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' and kodeorganisasi in ($arrUnit) order by induk, namaorganisasi asc";
$res=fetchdata($str);
foreach($res as $val){
	$key = $val['kodeorganisasi'];
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".getNamaOrg($d)."'>";
	}
	$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
	$n=$d;
	if($d!=$n){
		$optunit.="</optgroup>";
	}
}

echo"<table>
	<tr valign=moiddle>
		<td style='vertical-align:top'>
			<fieldset><legend>Form Preview</legend>
			<table cellpadding=1>
				<tr>
					<td>".$_SESSION['lang']['lokasitugas']."</td>
					<td>:</td>
					<td><select class=select2 id='unit' style=width:200px onchange=\"clearcontainer()\">".$optunit."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select class=select2 id='divisi' style=width:200px><option value=''>".$_SESSION['lang']['all']."</option></select></td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"preview(event,'html')\">".$_SESSION['lang']['preview']."</button>
						<button class=mybutton onclick=\"preview(event,'excel')\">".$_SESSION['lang']['excel']."</button>
						<!--<button class=mybutton onclick=\"clear()\">".$_SESSION['lang']['clear']."</button>-->
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td style='vertical-align:top'>
			<fieldset>
			<legend>Tambah data karyawan di Fingerprint</legend><button  style=width:260px class=mybutton><label style='color:blue;cursor:pointer' title='Klik untuk menambah karyawan finger' onclick=\"adddtfp(event)\">Tambah Data karyawan di Fingerprint</label></button>
			</fieldset>
			
			<fieldset>
			<legend>Ganti SN Fingerprint</legend><button class=mybutton style=width:260px><label style='color:blue;cursor:pointer;' title='Klik untuk SN Fingerprint' onclick=\"changesnfp(event)\">Ganti SN Fingerprint</label></button>
			</fieldset>
		</td>
		<td style='vertical-align:top'>";
		$str="select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res=fetchdata($str);
		if(count($res)>0 and $_SESSION['standard']['username']=='tim.owl7'){			
			echo"<fieldset>
				<legend>Upload datakaryawan dari server Fingerprint</legend><button style=width:260px class=mybutton style='color:blue;cursor:pointer' title='Klik untuk ambil karyawan dari server finger' onclick=\"addserverfp(event)\">Upload datakaryawan dari server Fingerprint</button>
				</fieldset>";
		}
		echo"</td>
	</tr>
</table>";

CLOSE_BOX();

OPEN_BOX();
echo"
<div style=clear:both></div>

<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container' table='sortable' >
			<img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		</a>
	</div><br>
	<div id='container' style='overflow:auto;height:55vh'; ></div>
</div>
";
CLOSE_BOX();

echo close_body();
?>
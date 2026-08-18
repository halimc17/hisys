<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<b>".getMenu('sdm_jobdesc')."</b>");
?>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script type='text/javascript' language='javascript' src='js/zMaster.js'></script>
<script type='text/javascript' language='javascript' src='js/zTools.js'></script>
<script type='text/javascript' language='javascript' src='js/sdm_jobdesc.js'></script>
<script type='text/javascript' language='javascript' src='js/rasterizeHTML.allinone.js'></script>
<link rel='stylesheet' type='text/css' href='style/fm.css'>
<?php
$_SESSION['jobdesc'] = array();

//Inisialisasi variable
$optramp = $optorganisasi = $optBuyer = $optKomoditi = $optByrke = $optsupplier = $optpabrik = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt = $optUnitKerja = $optSubBagian = $optunit = "<option value=''>".$_SESSION['lang']['all']."</option>";

### Get Unit
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' order by namaorganisasi asc";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";	
	$optPt.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";	
}

echo"<canvas id='myCanvas' width='400' height='200' style='display:none;background-color:#FFFFFF;'></canvas><table>
	<tr valign=middle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
		</td>
		<td>
			<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
		
		echo"<table><tr>";
		
		echo"<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td>
			<select id=caript style=width:175px;>".$optPt."</select>
		</td>";
		
		echo"</tr><tr>";
		
		echo"<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
		</td>";
		
		echo"</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button></td>
		</tr>";
		echo "</table>";
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div style='overflow:auto'>
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
				<tr align=center><td>".$_SESSION['lang']['notransaksi']."</td>
					<td>".$_SESSION['lang']['tanggalefektif']."</td>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>".$_SESSION['lang']['functionname']."</td>
					<td>".$_SESSION['lang']['departemen']."</td>
					<td>".$_SESSION['lang']['updateby']."</td>
					<td>".$_SESSION['lang']['status']."</td>
					<td colspan=4>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id=continerlist>
				<script>loadData(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	</fieldset>
</div>";

###===========================================================================
$optJabatan = $optDepartemen = $optKaryawan = $optAtasan = $optRekan = $optBawahan = $optPendidikan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
### Get Jabatan
$str="select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optJabatan.="<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";	
}

### Get Departemen
$str="select * from ".$dbname.".sdm_5departemen order by nama asc";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optDepartemen.="<option value='".$val['kode']."'>".$val['nama']."</option>";	
}

### Get Atasan
$str="select * from ".$dbname.".datakaryawan where (tanggalkeluar>= '".$tglskrg."' or tanggalkeluar = '0000-00-00')  and statuskaryawan != 'Keluar' ";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optAtasan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
	$optRekan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
	$optBawahan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
}

###Get Tipe Tanggung Jawab
$arrTgg = array('1'=>$_SESSION['lang']['rutin'],'2'=>$_SESSION['lang']['berkala'],'3'=>$_SESSION['lang']['insidentil']);
foreach($arrTgg as $key=>$val)
{
	$optTipeTgg.="<option value='".$key."'>".$val."</option>";
}

###Get Hubungan Kerja
$arrHubKer = array('1'=>$_SESSION['lang']['pihakinternal'],'2'=>$_SESSION['lang']['pihakeksternal']);
foreach($arrHubKer as $key=>$val)
{
	$optHubKer.="<option value='".$key."'>".$val."</option>";
}

###Get Pendidikan
$str="select * from ".$dbname.".sdm_5pendidikan order by levelpendidikan asc";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optPendidikan.="<option value='".$val['idpendidikan']."'>".$val['pendidikan']."</option>";
}

echo"<div id=formInput style=display:none;>";
echo"<table width='100%' cellspacing=15 cellpading=0>
	<tr>
		<td width='50%' style='vertical-align:top'>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['identitasjabatan']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['functionname']."</td>
					<td>:</td>
					<td>
						<select id='jabatan' style='width:200px;'>".$optJabatan."</select>
						<img id='jabatan_find' onclick=\"z.elSearch('jabatan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
					</td>			
				</tr>
				<tr>
					<td>".$_SESSION['lang']['departemen']."</td>
					<td>:</td>
					<td>
						<select id='departmen' style='width:200px;'>".$optDepartemen."</select>
						<img id='departmen_find' onclick=\"z.elSearch('departmen',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
					</td>			
				</tr>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td>
						<select id='unit' style='width:200px;' onchange='getkaryawanid()'>".$optUnit."</select>
						<img id='unit_find' onclick=\"z.elSearch('unit',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
					</td>			
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggalefektif']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tglefektif style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' readonly />
					</td>			
				</tr>
				<tr>
					<td>".$_SESSION['lang']['incumbent']."</td>
					<td>:</td>
					<td>
						<select id='karyawanid' style='width:200px;'>".$optKaryawan."</select>
						<img id='karyawanid_find' onclick=\"z.elSearch('karyawanid',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
					</td>			
				</tr>
			</table>
			</fieldset>
			<br>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['tujuanjabatan']."</legend>
				<table cellpading=1 cellspacing=1 border=0 class=sortable>
					<thead>
					<tr>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['deskripsi']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
					<tbody id='listtujuanjabatan'>
					</tbody>
					<tr>
						<td></td>
						<td>
							<input  type='text' class='myinputtext' id='tujuanjabatan' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" placeholder='add text' />
						</td>
						<td style='text-align:center'>
							<img src='images/skyblue/plus.png' class='zImgBtn' title='Add' onclick='addtujuanjabatan()'>
						</td>
					</tr>
					</thead>
				</table>
			</fieldset>
			<br>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['wewenang']."</legend>
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['deskripsi']."</td>
					<td>".$_SESSION['lang']['action']."</td>
				</tr>
				<tbody id='listwewenang'>
				</tbody>
				<tr>
					<td></td>
					<td>
						<input  type='text' class='myinputtext' id='wewenang' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" placeholder='add text' />
					</td>
					<td style='text-align:center'>
						<img src='images/skyblue/plus.png' class='zImgBtn' title='Add' onclick='addwewenang()'>
					</td>
				</tr>
				</thead>
			</table>
			</fieldset>
			<br>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['persyaratanjabatan']."</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['pendidikan']."</td>
						<td>:</td>
						<td>
							<select id='pendidikan' style='width:200px;'>".$optPendidikan."</select>
						</td>			
					</tr>
					<tr>
						<td>".$_SESSION['lang']['pengalamankerja']."</td>
						<td>:</td>
						<td>
							<input  type='text' class='myinputtext' id='pengalamankerja' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" />
						</td>			
					</tr>
					<tr>
						<td>".$_SESSION['lang']['pelatihan']."</td>
						<td>:</td>
						<td>
							<input  type='text' class='myinputtext' id='pelatihan' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" />
						</td>			
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kompetensi']."</td>
						<td>:</td>
						<td>
							<input  type='text' class='myinputtext' id='kompetensi' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" />
						</td>			
					</tr>
				</table>
			</fieldset>
		</td>
		
		<!--####################################################################################-->
		
		<td width='50%' style='vertical-align:top'>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['hubunganpelaporankerja']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['atasanlangsung']."</td>
					<td>:</td>
					<td>
						<select id='atasan' style='width:200px;'>".$optAtasan."</select>
						<img id='atasan_find' onclick=\"z.elSearch('atasan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
					</td>			
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['rekansederajat']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>
						<select id='rekan' style='width:200px;'>".$optRekan."</select>
						<img id='rekan_find' onclick=\"z.elSearch('rekan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
					</td>			
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['bawahanlangsung']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>
						<table cellpading=1 cellspacing=1 border=0 class=sortable>
							<thead>
							<tr>
								<td>".$_SESSION['lang']['nourut']."</td>
								<td>".$_SESSION['lang']['nama']."</td>
								<td>".$_SESSION['lang']['action']."</td>
							</tr>
							<tbody id='listbawahan'>
							</tbody>
							<tr>
								<td></td>
								<td>
									<select id='bawahan' style='width:200px;'>".$optBawahan."</select>
									<img id='bawahan_find' onclick=\"z.elSearch('bawahan',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'>
								</td>
								<td style='text-align:center'>
									<img src='images/skyblue/plus.png' class='zImgBtn' title='Add' onclick='addbawahan()'>
								</td>
							</tr>
							</thead>
						</table>
					</td>		
				</tr>
			</table>
			</fieldset>
			<br>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['tanggungjawab']."</legend>
				<table cellpading=1 cellspacing=1 border=0 class=sortable>
					<thead>
					<tr>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['tipe']."</td>
						<td>".$_SESSION['lang']['tugas']."</td>
						<td>".$_SESSION['lang']['indikatorkinerja']."</td>
						<td>".$_SESSION['lang']['deadline']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
					<tbody id='listtanggungjawab'>
					</tbody>
					<tr>
						<td></td>
						<td>
							<select id='tipetgg'>".$optTipeTgg."</select>
						</td>
						<td>
							<input  type='text' class='myinputtext' id='tugas' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" placeholder='add text' />
						</td>
						<td>
							<input  type='text' class='myinputtext' id='indkin' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" placeholder='add text' />
						</td>
						<td>
							<input type=text class=myinputtext id=deadline style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' readonly />
						</td>
						<td style='text-align:center'>
							<img src='images/skyblue/plus.png' class='zImgBtn' title='Add' onclick='addtanggungjawab()'>
						</td>
					</tr>
					</thead>
				</table>
			</fieldset>
			<br>
			<fieldset style='float:left;width:100%'><legend>".$_SESSION['lang']['hubungankerja']."</legend>
				<table cellpading=1 cellspacing=1 border=0 class=sortable>
					<thead>
					<tr>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['tipe']."</td>
						<td>".$_SESSION['lang']['deskripsi']."</td>
						<td>".$_SESSION['lang']['kegiatan']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
					<tbody id='listhubungankerja'>
					</tbody>
					<tr>
						<td></td>
						<td>
							<select id='tipehubker'>".$optHubKer."</select>
						</td>
						<td>
							<input  type='text' class='myinputtext' id='deskripsihubker' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" placeholder='add text' />
						</td>
						<td>
							<input  type='text' class='myinputtext' id='hubungankerja' style='width:196px;' onkeypress=\"return tanpa_kutip(event);\" placeholder='add text' />
						</td>
						<td style='text-align:center'>
							<img src='images/skyblue/plus.png' class='zImgBtn' title='Add' onclick='addhubungankerja()'>
						</td>
					</tr>
					</thead>
				</table>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan=2 style='text-align:center'>
			<input type='hidden' id='notransaksi' value=''>
			<input type='hidden' id='proses' value='insert'>
			<button class=mybutton onclick=savedata()>".$_SESSION['lang']['save']."</button>&nbsp;
			<button class=mybutton onclick=canceldata()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</div>";
CLOSE_BOX();
echo close_body(); 
?>
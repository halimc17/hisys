<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
  
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script   language=javascript1.2 src='js/vhc_project.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

/*
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
$str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where length(kodeorganisasi)=4
    order by induk, tipe, namaorganisasi";
}
else
{
    $str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'
    order by induk, tipe, namaorganisasi";
}
*/

 $optTipeBg=$optPekerjaan=$optSub="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
/*
    $str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where length(kodeorganisasi)=4 and kodeorganisasi  not like '%HO'
    order by induk, tipe, namaorganisasi";
*/
## List Pekerjaan
$arrStatus = getEnum($dbname,'project','pekerjaan');
foreach($arrStatus as $key)
{
	$optPekerjaan.="<option value='".$key."'>".$key."</option>";
}
## List Tipe Bg
$arrStatus = getEnum($dbname,'project','tipebg');
foreach($arrStatus as $key)
{
	$optTipeBg.="<option value='".$key."'>".$key."</option>";
}
 $str="select kodeorganisasi, namaorganisasi, induk from ".$dbname.".organisasi
    where length(kodeorganisasi)=4 and kodeorganisasi in (".getOrgDetail(2).") 
    order by induk, tipe, namaorganisasi";
    
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunitcr="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$res->fetch())
{
    $optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    $optunitcr.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}
if($_SESSION['language']=='EN'){
    $dd='namatipe1 as namatipe';
}else{

    $dd='namatipe';
}    
$str="select kodetipe, ".$dd." from ".$dbname.".sdm_5tipeasset order by kodetipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optaset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
    $optaset.="<option value='".$bar->kodetipe."'>".$bar->kodetipe." - ".$bar->namatipe."</option>";
}

#opsi filter List Data: tipe aset, jenis, status, tahun
$optasetcr = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$resAst = $owlPDO->query("select kodetipe, " . $dd . " from " . $dbname . ".sdm_5tipeasset order by kodetipe");
$resAst->setFetchMode(PDO::FETCH_OBJ);
while ($barAst = $resAst->fetch()) {
    $optasetcr .= "<option value='" . htmlspecialchars($barAst->kodetipe, ENT_QUOTES) . "'>" . htmlspecialchars($barAst->kodetipe . " - " . $barAst->namatipe) . "</option>";
}
$optjeniscr = "<option value=''>" . $_SESSION['lang']['all'] . "</option><option value='AK'>AK - Aktiva Dalam Konstruksi</option><option value='PB'>PB - Pabrikasi</option>";
$optpekerjaancr = "<option value=''>" . $_SESSION['lang']['all'] . "</option><option value='Internal'>Internal</option><option value='External'>External</option>";
$optstatuscr = "<option value=''>" . $_SESSION['lang']['all'] . "</option><option value='0'>Belum Diposting</option><option value='1'>Sudah Diposting</option>";
$opttahuncr = "<option value=''>Semua Tahun</option>";
$resThn = $owlPDO->query("select distinct year(tanggalmulai) as thn from " . $dbname . ".project where tanggalmulai > '0000-00-00' order by thn desc");
$resThn->setFetchMode(PDO::FETCH_OBJ);
while ($barThn = $resThn->fetch()) {
    $opttahuncr .= "<option value='" . (int)$barThn->thn . "'>" . (int)$barThn->thn . "</option>";
}

$kamusjenis['AK']='Aktiva Dalam Konstruksi / Activa Under Construction';
$kamusjenis['PB']='Pabrikasi';

$optjenis="";
$arrjenis=getEnum($dbname,'project','tipe');
foreach($arrjenis as $kei=>$fal)
{
    if($fal=='PB')
    {
     #Pabrikasi  belum aktif  karena akunnya belum ada, pastikan akunnya sudah ada dan didaftar  pada parameter jurnal dengan kode
    #PAB       
    } 
    else{
          $optjenis.="<option value='".$kei."'>".$fal." ".$kamusjenis[$fal]."</option>";
    }
    
} 	

$optKel="";
$arrKel=getEnum($dbname,'project','kelompok');
foreach($arrKel as $kel)
{
   
	$optKel.="<option value='".$kel."'>".$kel."</option>";
  
} 

$optSatuan = makeOption($dbname,'setup_satuan','satuan,satuan');
$optsat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach($optSatuan as $kel){
	$optsat.="<option value='".$kel."'>".$kel."</option>";
}
//jenis biaya
$optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$orgOption="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)='4'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $orgOption.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
    
}

OPEN_BOX('','<span class=judul>'.getMenu('vhc_project').'</span><br>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>	 
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=resetFilterCIP();displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
		<td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
			<style>
				#filterTabelCIP td {padding:2px 3px;vertical-align:middle}
				#filterTabelCIP td.lbl {white-space:nowrap}
				#filterTabelCIP td.lbl2 {white-space:nowrap;padding-left:15px}
			</style>
			<table id=filterTabelCIP border=0 cellspacing=1>
				<tr>
					<td class=lbl>".$_SESSION['lang']['unit']."</td><td>:</td>
					<td><select id='unitcr' class='select2' style='width:230px;'>".$optunitcr."</select></td>
					<td class=lbl2>".$_SESSION['lang']['jenis']."</td><td>:</td>
					<td><select id='jeniscr' class='select2' style='width:230px;'>".$optjeniscr."</select></td>
					<td class=lbl2>".$_SESSION['lang']['aset']."</td><td>:</td>
					<td><select id='asetcr' class='select2' style='width:230px;'>".$optasetcr."</select></td>
				</tr>
				<tr>
					<td class=lbl>Int / Ext</td><td>:</td>
					<td><select id='pekerjaancr' class='select2' style='width:230px;'>".$optpekerjaancr."</select></td>
					<td class=lbl2>Status</td><td>:</td>
					<td><select id='statuscr' class='select2' style='width:230px;'>".$optstatuscr."</select></td>
					<td class=lbl2>".$_SESSION['lang']['tahun']."</td><td>:</td>
					<td><select id='tahuncr' class='select2' style='width:230px;'>".$opttahuncr."</select></td>
				</tr>
				<tr>
					<td class=lbl>".$_SESSION['lang']['kode']."</td><td>:</td>
					<td><input type=text id='kodecr' class='myinputtext' style='width:226px;' onkeypress=\"if(event.keyCode==13){loadData(0);return false;}return tanpa_kutip(event);\" /></td>
					<td class=lbl2>".$_SESSION['lang']['nama']."</td><td>:</td>
					<td><input type=text id='namacr' class='myinputtext' style='width:226px;' onkeypress=\"if(event.keyCode==13){loadData(0);return false;}return tanpa_kutip(event);\" /></td>
					<td colspan=3 class=lbl2>
						<button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button>
						<button class=mybutton onclick=exportExcelCIP()>" . $_SESSION['lang']['excel'] . "</button>
						<button class=mybutton onclick=exportPdfCIP()>PDF</button>
					</td>
				</tr>
			</table>
		</fieldset>
	</td>
	</tr>
</table></div>";
CLOSE_BOX();
echo"<div id=forminput style=display:none>";
OPEN_BOX();
echo"<fieldset style='float:left;'>
    <legend>Form</legend>
    <table cellspacing=1 border=0>
	<tr>
		<td align=right>
	        ".$_SESSION['lang']['kodecapex']."
	    </td>
	    <td>:</td>
	    <td>
	        <input type=text id=kodecapex class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:170px;' disabled>
			<img src='images/zoom.png' title='Cari' id='imgsearch' class='resicon' onclick=searchnocapex('Cari',event)>
			<img src='images/application/application_delete.png' id='imgdelete' class='resicon' title='Delete' onclick=batal();>
	    </td>

	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['unitkerja']."
	    </td>
	    <td>:</td>
	    <td>
	        <select id=unit class=select2 style='width:200px;' onchange='getjbiaya()'>".$optunit."</select>
	    </td>

	    <td class='bintang'>".$_SESSION['lang']['posisiasset']."</td>
	    <td>:</td>
		<td>
			<select class=select2 style=width:195px id=posisiasset onchange='changetipelokasi()'>".$orgOption."</select>
		</td>
	</tr>

    <tr>
	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['aset']."
	    </td>
	    <td>:</td>
	    <td>
	        <select id=aset class=select2 onchange=getSub() style='width:200px;'>".$optaset."</select>
	    </td>
    
	    <td class='bintang' align=left>
	        Sub Asset
	    </td>
	    <td>:</td>
	    <td>
	        <select id=sub class=select2 style='width:200px;'>".$optSub."</select>
	    </td>

		<td class='bintang'>Tipe Lokasi Asset</td>
		<td>:</td>
		<td>
			<select class=select2 style=width:195px id=tipelokasiasset ></select>
		</td>
	</tr>

    <tr>
	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['jenis']."
	    </td>
	    <td>:</td>
	    <td>
	        <select id=jenis class=select2 style='width:200px;'>".$optjenis."</select>
	    </td>

	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['jenisbiaya']."
	    </td>
	    <td>:</td>
	    <td>
	        <select class=select2 style=width:200px id=jenisbiaya>".$optjb."</select>
	    </td>

		<td>No Mesin</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext  id=nomesin style=width:192px   onkeypress=\"return tanpa_kutip(event)\">
		</td>
	</tr>

	<tr>
		<td class='bintang' align=left>".$_SESSION['lang']['tipe']."</td>
		<td>:</td>
		<td>
			<select id=tipebg class=select2 style='width:200px;'>".$optTipeBg."</select>
		</td>

		<td class='bintang' align=left >".$_SESSION['lang']['pekerjaan']."</td>
		<td>:</td>
		<td>
			<select id=pekerjaan class=select2 style='width:200px;'>".$optPekerjaan."</select>
		</td>

		<td>No Rangka</td>
		<td>:</td>
		<td><input type=text class=myinputtext  id=norangka style=width:192px   onkeypress=\"return tanpa_kutip(event)\"></td>
	</tr>

    <tr>
	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['nama']."
	    </td>
	    <td>:</td>
	    <td>
	        <input type=text id=nama onkeydown=\"upperCaseF(this)\" class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:195px;'>
	    </td>
    
		<td class='bintang' align=left>".$_SESSION['lang']['satuan']."</td>
		<td>:</td>
		<td class='bintang'>
        	<select id=satuan class=select2 style='width:75px;'>".$optsat."</select>
    
	        &nbsp;".$_SESSION['lang']['jumlah']."&nbsp;
			<input type=text id=jumlah style='width:67px;' class=myinputtextnumber onkeypress='return angka_doang(event)'/>
	    </td>	

		<td class='bintang' align=left>
	        ".$_SESSION['lang']['tanggal']."
	    </td>
	    <td>:</td>
	    <td>
	        <input style='width:85px;' id=tanggalmulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')." readonly>
	    
	        s/d <input style='width:80px;' id=tanggalselesai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')." readonly>
	    </td>
	</tr>
	
	<tr>
		<td class='bintang'>Tipe Model</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tipemodel style=width:195px maxlength=100  onkeypress=\"return tanpa_kutip(event)\"></td>

		<td class='bintang'>".$_SESSION['lang']['keterangan']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=keterangan style=width:192px maxlength=100  onkeypress=\"return tanpa_kutip(event)\"></td>

		<td hidden>Dengan Approval</td>
		<td hidden>:</td>
		<td hidden><input type=checkbox id=dgnapprvl onchange='cekapproval()'></td>
	</tr>";
    ## INPUT APPROVAL JIKA DENGAN APPROVAL
    $str="SELECT karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan='0' and (tanggalkeluar!= '0000-00-00' or tanggalkeluar < '".date('Y-m-d')."') and statuskaryawan!='kontrak' and statuskaryawan!='keluar' and namakaryawan not like 'ADMINISTRATOR%' ORDER BY namakaryawan";
		$optPersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rstr=$res->fetch()){ 
			$optPersetujuan.="<option value=".$rstr['karyawanid'].">".$rstr['namakaryawan']."</option>"; 
		} 
                        #Persetujuan 1-3 sejajar dengan kolom form, Persetujuan 4 di baris berikutnya; tampil hanya bila proyek memakai approval
            echo"<tbody id=formapproval hidden>";
            foreach (array(array(1,2,3), array(4)) as $barisApr) {
                echo"<tr>";
                foreach ($barisApr as $i) {
                    echo"<td align=left>".$_SESSION['lang']['persetujuan']." ".$i."</td>
                    <td>:</td>
                    <td><select id=aprv".$i." class=select2 style='width:200px;'>" . $optPersetujuan . "</select></td>";
                }
                echo"</tr>";
            }
            echo"</tbody>";
	echo"<tr>
        <td colspan=9 style='padding-top:8px;'>
            <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
            <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
            <input type=hidden value=insert id=method>
            <input type=hidden value='' id=kode>
        </td>
    </tr>
    </table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=formdetailinput>";
OPEN_BOX();
echo "
	 <div class='table-scroll' id=dataDisimpan style='height:68vh;width:100%;'>
      <table class=sortable border=0 cellspacing=1 cellpadding=5 style='width:100%;'>
	  <thead> 
	  <tr>
	  <th align=center>".$_SESSION['lang']['kodecapex']."</th>
	  <th align=center>".$_SESSION['lang']['unit']."</th>
	  <th align=center>".$_SESSION['lang']['jenis']."</th>
	  <th align=center>".$_SESSION['lang']['nama']."</th>
	  <th align=center>Int / Ext</th>
	  <th align=center>".$_SESSION['lang']['satuan']."</th>
	  <th align=center>".$_SESSION['lang']['jumlah']."</th>
	  <th align=center>".$_SESSION['lang']['tanggalmulai']."</th>
	  <th align=center>".$_SESSION['lang']['tanggalsampai']."</th>
	  <th align=center>".$_SESSION['lang']['nilai']."</th>
	  <th align=center>".$_SESSION['lang']['kodeasset']."</th>
	  <th align=center>".$_SESSION['lang']['updateby']."</th>
	  <th align=center colspan=5>".$_SESSION['lang']['action']."</th>
	  <th align=center colspan=3>".$_SESSION['lang']['print']."</th>
	  </tr>
	  </thead>
	  <tbody id=container>"; 
echo"<script>loadData(0)</script>";

echo "</tbody>
    <tfoot id='footData'>
    </tfoot>
    </table>
</div>";

echo"<div id=detailInputAK style=display:none>";
$frmdt="<fieldset id='excapex1' style=min-width:800px;><legend>".$_SESSION['lang']['detail']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
$frmdt.="<thead><tr><td align=center>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td align=center>No Mesin</td>";
$frmdt.="<td align=center>No Rangka/Casis</td>";
$frmdt.="<td align=center>Tahun<br>Produksi</td>";
$frmdt.="<td align=center>Tahun<br>Perolehan</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['action']."</td></tr></thead><tbody>";
$frmdt.="<tr class=rowcontent><td><input type=text id=kdProj class=myinputtext maxlength=20 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'  disabled></td>";
$frmdt.="<td id=kegiatanx hidden></td>";
$frmdt.="<td><input type=text id=nomesin class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:200px;'></td>";
$frmdt.="<td><input type=text id=norangka class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:200px;'></td>";
$frmdt.="<td><input type=text id=tahunproduksi class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:70px;'></td>";
$frmdt.="<td><input type=text id=tahunprolehan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:70px;'></td>";
$frmdt.="<td style='width:40px;' align=center><img src='images/save.png' class='zImgBtn' style='cursor:pointer;' onclick=addDetailAK() /></td></tr></tbody></table> <button class=mybutton onclick=doneSlsi()>".$_SESSION['lang']['selesai']."</button></fieldset><input type=hidden id=kegId />";

$frmdt.="<fieldset style=min-width:800px;><legend>".$_SESSION['lang']['list']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
$frmdt.="<thead><tr><td align=center>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td align=center>No Mesin</td>";
$frmdt.="<td align=center>No Rangka/Casis</td>";
$frmdt.="<td align=center>Tahun Produksi</td>";
$frmdt.="<td align=center>Tahun Prolehan</td>";
$frmdt.="<td align=center style='width:50px;'>".$_SESSION['lang']['action']."</td></tr></thead><tbody id=printDat>";
$frmdt.="</tbody></table></fieldset>";
$frmdt.="</div>";

$frmdt.="<div id=detailInput style=display:none>";
#kolom tabel input dan tabel list dibuat identik (lebar tetap) supaya sejajar
$colKeg = "<colgroup><col style='width:120px'><col style='width:260px'><col style='width:260px'><col style='width:95px'><col style='width:85px'><col style='width:85px'><col style='width:110px'><col style='width:110px'><col style='width:120px'></colgroup>";
$frmdt.="<fieldset id='excapex1x' style='min-width:800px;width:fit-content;max-width:100%;'><legend>".$_SESSION['lang']['detail']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:1245px;max-width:100%;table-layout:fixed;'>".$colKeg;
$frmdt.="<thead><tr><td align=center>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['deskripsi']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['namakegiatan']." <span style='color:#c0392b'>*</span></td>";

$frmdt.="<td align=center>".$_SESSION['lang']['satuan']." <span style='color:#c0392b'>*</span></td>";
$frmdt.="<td align=center>".$_SESSION['lang']['volume']." <span style='color:#c0392b'>*</span></td>";
$frmdt.="<td align=center>".$_SESSION['lang']['bobot']." % <span style='color:#c0392b'>*</span></td>";

$frmdt.="<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['tanggalsampai']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['action']."</td></tr></thead><tbody>";
$frmdt.="<tr class=rowcontent><td><input type=text id=kdProjx class=myinputtext maxlength=20 onkeypress=\"return tanpa_kutip(event);\" style='width:100%;box-sizing:border-box;' disabled></td>";
$frmdt.="<td><input type=text id=deskripsi class=myinputtext maxlength=500 onkeypress=\"return tanpa_kutip(event);\" style='width:100%;box-sizing:border-box;'></td>";
$frmdt.="<td><input type=text id=namaKeg class=myinputtext maxlength=500 onkeypress=\"return tanpa_kutip(event);\" style='width:100%;box-sizing:border-box;'></td>";

$frmdt.="<td><select id=satKeg class=select2 style='width:100%;'>".$optsat."</select></td>";
$frmdt.="<td><input type=text id=volKeg onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style='width:100%;box-sizing:border-box;'></td>";
$frmdt.="<td><input type=text id=bobotKeg onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style='width:100%;box-sizing:border-box;'></td>";


$frmdt.="<td><input style='width:100%;box-sizing:border-box;' id=tanggalMulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')." readonly></td>";
$frmdt.="<td><input style='width:100%;box-sizing:border-box;' id=tanggalSampai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')." readonly></td>";
$frmdt.="<td align=center><img src='images/save.png' class='zImgBtn' title='".$_SESSION['lang']['save']."' style='cursor:pointer;' onclick=addDetail() /></td></tr></tbody></table> <button class=mybutton onclick=batalKeg()>".$_SESSION['lang']['cancel']."</button> <button class=mybutton onclick=doneSlsi()>".$_SESSION['lang']['selesai']."</button></fieldset><input type=hidden id=kegId />";


$frmdt.="<fieldset style='min-width:800px;width:fit-content;max-width:100%;'><legend>".$_SESSION['lang']['list']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:1245px;max-width:100%;table-layout:fixed;'>".$colKeg;
$frmdt.="<thead><tr><td align=center>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['deskripsi']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['namakegiatan']."</td>";

$frmdt.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['volume']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['bobot']." %</td>";

$frmdt.="<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['tanggalsampai']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['action']."</td></tr></thead><tbody id=printDatx>";
$frmdt.="</tbody></table></fieldset>";
$frmdt.="</div>";
echo $frmdt;
echo"</div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>
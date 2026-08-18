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
    where length(kodeorganisasi)=4 
    order by induk, tipe, namaorganisasi";
    
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
    $optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
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
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
		<td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
			<table> 
				<tr>
					<td>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><input type=text id='namacr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>
					<td>".$_SESSION['lang']['unit'] . "</td>
					<td>:</td>
					<td><input type=text class='myinputtext' id='unitcr'  size='12' maxlength='10' /></td>
					<td>".$_SESSION['lang']['kode']."</td>
					<td>:</td>
					<td><input type=text id='kodecr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>
				</tr><tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td>
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
	        <select id=unit style='width:200px;' onchange='getjbiaya()'>".$optunit."</select>
	    </td>

	    <td class='bintang'>".$_SESSION['lang']['posisiasset']."</td>
	    <td>:</td>
		<td>
			<select style=width:195px id=posisiasset onchange='changetipelokasi()'>".$orgOption."</select>
		</td>
	</tr>

    <tr>
	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['aset']."
	    </td>
	    <td>:</td>
	    <td>
	        <select id=aset onchange=getSub() style='width:200px;'>".$optaset."</select>
	    </td>
    
	    <td class='bintang' align=left>
	        Sub Asset
	    </td>
	    <td>:</td>
	    <td>
	        <select id=sub style='width:200px;'>".$optSub."</select>
	    </td>

		<td class='bintang'>Tipe Lokasi Asset</td>
		<td>:</td>
		<td>
			<select  style=width:195px id=tipelokasiasset ></select>
		</td>
	</tr>

    <tr>
	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['jenis']."
	    </td>
	    <td>:</td>
	    <td>
	        <select id=jenis style='width:200px;'>".$optjenis."</select>
	    </td>

	    <td class='bintang' align=left>
	        ".$_SESSION['lang']['jenisbiaya']."
	    </td>
	    <td>:</td>
	    <td>
	        <select style=width:200px id=jenisbiaya>".$optjb."</select>
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
			<select id=tipebg style='width:200px;'>".$optTipeBg."</select>
		</td>

		<td class='bintang' align=left >".$_SESSION['lang']['pekerjaan']."</td>
		<td>:</td>
		<td>
			<select id=pekerjaan style='width:200px;'>".$optPekerjaan."</select>
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
        	<select id=satuan style='width:75px;'>".$optsat."</select>
    
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
            echo"<div > <table id=formapproval hidden> "; 
            for ($i=1; $i <=4 ; $i++) { 
                echo"<tr>
                <td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
                <td>:</td>
                <td><select id=aprv".$i." style=\"width:155px;\">" . $optPersetujuan . "</select></td>
                </tr>";
            }  
            echo" </table></div>";
	echo"<td><td><td colspan=2>
    <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
    <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>	 
    <input type=hidden value=insert id=method>
    <input type=hidden value='' id=kode>
	
    </table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=formdetailinput>";
OPEN_BOX();
echo "
	 <div class='table-scroll' id=dataDisimpan>
      <table class=sortable border=0 cellspacing=1 cellpadding=5>
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
$frmdt.="<td align=center style='width:50px;'>".$_SESSION['lang']['action']."</td></tr></thead><tbody >";
$frmdt.="</tbody></table></fieldset>";
$frmdt.="</div>";

$frmdt.="<div id=detailInput style=display:none>";
$frmdt.="<fieldset id='excapex1' style=min-width:800px;><legend>".$_SESSION['lang']['detail']."</legend>";
$frmdt.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
$frmdt.="<thead><tr><td align=center>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['deskripsi']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['namakegiatan']."</td>";

$frmdt.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['volume']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['bobot']." %</td>";

$frmdt.="<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['tanggalsampai']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['action']."</td></tr></thead><tbody>";
$frmdt.="<tr class=rowcontent><td><input type=text id=kdProjx class=myinputtext maxlength=20 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'  disabled></td>";
$frmdt.="<td><input type=text id=deskripsi class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:250px;'></td>";
$frmdt.="<td><input type=text id=namaKeg class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:250px;'></td>";
 
$frmdt.="<td>".makeElement('satKeg','select',"",array(),$optSatuan)."</td>";
$frmdt.="<td><input type=text id=volKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";
$frmdt.="<td><input type=text id=bobotKeg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";


$frmdt.="<td><input style='width:80px;' id=tanggalMulai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')." readonly></td>";
$frmdt.="<td><input style='width:80px;' id=tanggalSampai class=myinputtext maxlength=10 onkeypress=\"return false;\" size=10 onmousemove=setCalendar(this.id) value=".date('d-m-Y')." readonly></td>";
$frmdt.="<td style='width:40px;' align=center><img src='images/save.png' class='zImgBtn' style='cursor:pointer;' onclick=addDetail() /></td></tr></tbody></table> <button class=mybutton onclick=doneSlsi()>".$_SESSION['lang']['selesai']."</button></fieldset><input type=hidden id=kegId />";


$frmdt.="<fieldset style=min-width:800px;><legend>".$_SESSION['lang']['list']."</legend>";
$frmdt.="<table style=min-width:1030px; cellpadding=1 cellspacing=1 border=0 class=sortable>";
$frmdt.="<thead><tr><td align=center style='width:100px;'>".$_SESSION['lang']['kode']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['deskripsi']."</td>";
$frmdt.="<td align=center>".$_SESSION['lang']['namakegiatan']."</td>";

$frmdt.="<td align=center style='width:70px;'>".$_SESSION['lang']['satuan']."</td>";
$frmdt.="<td align=center style='width:50px;'>".$_SESSION['lang']['volume']."</td>";
$frmdt.="<td align=center style='width:50px;'>".$_SESSION['lang']['bobot']." %</td>";

$frmdt.="<td align=center style='width:90px;'>".$_SESSION['lang']['tanggalmulai']."</td>";
$frmdt.="<td align=center style='width:90px;'>".$_SESSION['lang']['tanggalsampai']."</td>";
$frmdt.="<td align=center style='width:80px;'>".$_SESSION['lang']['action']."</td></tr></thead><tbody id=printDatx>";
$frmdt.="</tbody></table></fieldset>";
$frmdt.="</div>";
echo $frmdt;
echo"</div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>
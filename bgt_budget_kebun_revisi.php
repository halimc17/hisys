<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';
?>
<script>pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";</script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/bgt_budget_kebun_revisi.js?v=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<script>dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";</script>
<?php
$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optAfdeling="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optBlok;
$optKdbdgt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg2="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget like '%SDM%' order by nama asc";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$qOrg2->fetch()){
    $optKdbdgt.="<option value=".$rOrg2['kodebudget'].">".$rOrg2['nama']."</option>";
}

$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sKeg="select distinct kodekegiatan,namakegiatan,kelompok from ".$dbname.".setup_kegiatan where  kelompok in ('PNN','TBM','TM','BBT','TB') and status='1' order by kodekegiatan asc";
$qKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
$qKeg->setFetchMode(PDO::FETCH_ASSOC);
while($rKeg=$qKeg->fetch()){
    if(!empty($kegId)){
        $optKeg.="<option value=".$rKeg['kodekegiatan']." ".($rKeg['kodekegiatan']==$kegId?'selected':'').">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." [".$rKeg['kelompok']."]</option>";
    }else{
        $optKeg.="<option value=".$rKeg['kodekegiatan'].">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." [".$rKeg['kelompok']."]</option>";
    }
}

$arrupd=array('1'=>'Data sudah ada (update)','2'=>'Data baru (insert)','3'=>'Semua data (update dan insert)');
foreach($arrupd as $key => $val){
	if($key=='2'){
		@$optupdate.="<option value=".$key." selected>".$val."</option>";
	}else{
		@$optupdate.="<option value=".$key.">".$val."</option>";
	}	
}

OPEN_BOX('','<span class=judul>'.getMenu('bgt_budget_kebun_revisi').'</span>');
echo"<br/><fieldset style='float:left;'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
		<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['budgetyear']."</td>
			<td><input type='text' class='myinputtextnumber' id='thnBudget' style='width:150px;' maxlength='4' onkeypress='return angka_doang(event)' onblur='getKodeblok(0,0,0)' /></td>
			
			<td>".$_SESSION['lang']['noakun']."</td>
			<td><input type='text' class='myinputtextnumber' id='noAkun' disabled style='width:150px;' onkeypress='return angka_doang(event)' /></td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td><input type='text' class='myinputtext' disabled value='ESTATE' id='tipeBudget' style=width:150px; /></td>
			
			
			<td>".$_SESSION['lang']['fisik']."</td>
			<td><input type='text' class='myinputtextnumber' id='volKeg' style='width:150px;' onkeypress='return angka_doang(event)' /></td>
		
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td><select style='width:155px;' id='kdBlok' onchange=isiLuas(this)>".$optBlok."</select>
				<img id='kdBlok' onclick=z.elSearch('kdBlok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			</td>
			
			<td>".$_SESSION['lang']['satuan']."</td>
			<td><input type='text' disabled class='myinputtext' id='satKeg' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tahuntanam']."</td>
			<td><select style='width:155px;' id='tt' onchange=gantiKegiatan()></select>
				<img id='tt' onclick=z.elSearch('tt',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			</td>
			
			<td>".$_SESSION['lang']['rotasi']." / ".$_SESSION['lang']['tahun']."</td>
			<td><input type='text' class='myinputtextnumber' id='rotThn' style='width:150px;' onkeypress='return tanpa_kutip(event)' value='1' /></td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kegiatan']."</td>
			<td><select style='width:155px;' id='kegId' onchange='getSatuan()'>".$optKeg."</select>
				<img id='kegId' onclick=z.elSearch('kegId',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			</td>
		
			<td hidden>Update</td>
			<td hidden><select style='width:155px;' id='update'>".$optupdate."</select></td>
		</tr>
		<tr>
			<td></td>
			<td colspan='2'>
			<button class=\"mybutton\"  id=\"saveData\" onclick='saveData()'>".$_SESSION['lang']['save']."</button>
			<button  class=\"mybutton\"  id=\"newData\" onclick='newData()'>".$_SESSION['lang']['baru']."</button>
			<button  class=\"mybutton\"  id=\"reload\" onclick='reloadframe()'>Refresh</button></td>
		</tr>
		</table></fieldset>";

$optThnTtp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

echo"<fieldset  style='display:none'><legend>".$_SESSION['lang']['tutup']."</legend>
    <div><table><tr><td>".$_SESSION['lang']['budgetyear']."</td><td><select id='thnBudgetTutup' style='width:150px'>".$optThnTtp."</select></td></tr>";
echo"<tr><td colspan=2 align=center><button class=\"mybutton\"  id=\"saveData\" onclick='closeBudget()'>".$_SESSION['lang']['tutup']."</button></td></tr></table>";
echo"</div></fieldset>";



echo"<fieldset id=vhcPendingFieldset style='display:none'><legend>Data Kendaraan belum ditutup</legend><div>";
echo"<table><thead><tr class=rowheader>";
echo"<td>".$_SESSION['lang']['nomor']."</td>";
echo"<td>".$_SESSION['lang']['kode']."</td>";
echo"<td>".$_SESSION['lang']['nama']."</td>";
echo"<td>Jam Tersedia</td>";
echo"<td>Jam Teralokasi</td>";
echo"<td>Sisa</td>";
echo"</tr></thead><tbody id=vhcPendingList>";
echo"</tbody></table>";
echo"</div></fieldset>";

$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' order by a.noaruskas asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
}


$frm[0].="<fieldset><legend>".$_SESSION['lang']['sdm']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
<tr>
	<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
	<td><select id='aruskassdm' style='width:155px;'>".$optaruskas."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td>
	<td><select id='kdBudget' style='width:155px;' onchange='jumlahkan(1)'>".$optKdbdgt."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['hkefektif']."</td><td>:</td><td><input disabled class='myinputtextnumber'  style='width:150px;' id='hkEfektif' /></td>
</tr>
<tr>
	<td>Norma / Sat / Rot</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:150px;' id='norma_1' onkeyup='kalikannorma(1)' onkeypress='return angka_doang(event)' value='0' /></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['jhk']."</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:150px;' id='jmlh_1' onkeyup='jumlahkan(1)' disabled onkeypress='return angka_doang(event)' value='0' /></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['totalbiaya']." ".$_SESSION['lang']['setahun']."</td>
	<td>:</td>
	<td><input type='text' class='myinputtextnumber'  style='width:150px;' id='totBiaya' value='0' onkeypress='return false' /></td>
</tr>
<tr>
	<td></td><td></td><td colspan=3>
	<button class=mybutton id=btlTmbl name=btlTmbl onclick=saveBudget(1)  >".$_SESSION['lang']['save']."</button></td></tr></table>
";
$frm[0].="</fieldset>";
CLOSE_BOX();

echo"<div id='listDatHeader' style='display:block'>";
OPEN_BOX();
$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
$sThn="select distinct tahunbudget from ".$dbname.".bgt_budget where substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' and tipebudget='ESTATE' and kodebudget!='UMUM' order by tahunbudget desc";
$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while($rThn=$qThn->fetch()){
    $optTahunBudgetHeader.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}
$optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBlok="select distinct kodeblok from ".$dbname.".bgt_blok where kodeblok like '".$_SESSION['empl']['lokasitugas']."%'order by kodeblok asc";
$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
$qBlok->setFetchMode(PDO::FETCH_ASSOC);
while($rBlok=$qBlok->fetch()){
    $optBlok.="<option value='".$rBlok['kodeblok']."'>".$rBlok['kodeblok']."</option>";
}
$optAkun="<option value=''>".$_SESSION['lang']['all']."</option>";
$sAkun="select distinct a.noakun,b.namaakun from ".$dbname.".bgt_budget a
        left join ".$dbname.".keu_5akun b on a.noakun=b.noakun
        where tipebudget='ESTATE' and kodebudget!='UMUM' order by noakun asc";
$qAkun=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$qAkun->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$qAkun->fetch()){
    $optAkun.="<option value='".$rAkun['noakun']."'>".$rAkun['noakun']."-".$rAkun['namaakun']."</option>";
}

$optAkunkeg="<option value=''>".$_SESSION['lang']['all']."</option>";
$sAkun="select distinct a.kegiatan,b.namakegiatan from ".$dbname.".bgt_budget a
        left join ".$dbname.".setup_kegiatan b on a.kegiatan=b.kodekegiatan
        where tipebudget='ESTATE' and kodebudget!='UMUM' order by kegiatan asc";
$qAkun=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$qAkun->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$qAkun->fetch()){
    $optAkunkeg.="<option value='".$rAkun['kegiatan']."'>".$rAkun['kegiatan']."-".$rAkun['namakegiatan']."</option>";
}
    
echo"<fieldset><legend>List Data</legend><div>
		<table border=0>
		<tr><td>".$_SESSION['lang']['budgetyear']."<td>:</td><td><select id='thnbudgetHeader' style='width:150px;' onchange='ubah_list()'>".$optTahunBudgetHeader."</select></td><td></td>
		<td>".$_SESSION['lang']['blok']."<td>:</td><td><select id=kdBlokCari style='width:150px;' onchange='ubah_list()'>".$optBlok."</select></td>
		<td><img id='kdBlokCari' onclick=z.elSearch('kdBlokCari',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
		
		
		</tr>
		<tr>
		<td>".$_SESSION['lang']['noakun']."<td>:</td><td><select id=noakunCari style='width:150px;' onchange='ubah_list()'>".$optAkun."</select></td>
		
		<td><img id='noakunCari' onclick=z.elSearch('noakunCari',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
		
		<td>".$_SESSION['lang']['kegiatan']."<td>:</td><td><select id=nokegiatanCari style='width:150px;' onchange='ubah_list()'>".$optAkunkeg."</select></td>
		<td><img id='nokegiatanCari' onclick=z.elSearch('nokegiatanCari',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
		

		</tr>
		<tr>
		<td></td><td></td>
		<td><button class=mybutton onclick=ubah_list()>".$_SESSION['lang']['preview']."</button>
		    <button class=mybutton onclick=ubah_list_excel('excel')>".$_SESSION['lang']['excel']."</button></td>
		</tr>
		
		
		
		</table></div><div style=clear:both></div><hr>";
echo"<div id='listDatHeader2'>";
echo"<script>dataHeader()</script></div>";
CLOSE_BOX();
echo"</div></fieldset>";

echo"<div id='formIsian' style='display:none;'>";
OPEN_BOX();
$frm[0].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
    <table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
			<td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tipeBudget']."</td>
            <td align=center>".$_SESSION['lang']['kodeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kegiatan']."</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
			<td align=center>".$_SESSION['lang']['aruskas']."</td>
             <td align=center width=50px>".$_SESSION['lang']['rotasi']." / ".$_SESSION['lang']['tahun']."</td>
            <td align=center>".$_SESSION['lang']['volume']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['rp']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>Action</td>
            </tr>
            </thead><tbody id=containDataSDM> 
		";
$frm[0].="</tbody></table></fieldset>";
$optKdbdgtM="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrgm="select kodebudget,nama from ".$dbname.".bgt_kode where substr(kodebudget,1,1)='M' order by kodebudget asc";
$qOrgm=$owlPDO->query($sOrgm) or die(print " Gagal: ".PDOException::getMessage());
$qOrgm->setFetchMode(PDO::FETCH_ASSOC);
while($rOrgm=$qOrgm->fetch()){
    $optKdbdgtM.="<option value='".$rOrgm['kodebudget']."'>".$rOrgm['kodebudget']." [".$rOrgm['nama']."]</option>";
}
$frm[1].="<fieldset><legend>".$_SESSION['lang']['material']."</legend>";
$frm[1].="<table cellspacing=1 border=0>
<tr>
	<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
	<td><select id='aruskasmat' style='width:155px;'>".$optaruskas."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['kodeanggaran']."</td>
	<td>:</td>
	<td><select id='kdBudgetM' style='width:153px;' onchange='getKlmpkbrg()'>".$optKdbdgtM."</select>
		<img id='kdBudgetM' onclick=z.elSearch('kdBudgetM',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['kodebarang']."</td><td>:</td><td><input type='text' class='myinputtext' id='kdBarang' style='width:150px;' onkeypress='return angka_doang(event)' />&nbsp;<img src='images/skyblue/zoom.png' style='position:relative;top:2px;' class=\"resicon\" title='".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."' onclick=\"searchBrg('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."','<fieldset><legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>".$_SESSION['lang']['find']."&nbsp;<input type=text class=myinputtext id=nmBrg><button class=mybutton onclick=findBrg()>".$_SESSION['lang']['find']."</button></fieldset><div id=containerBarang style=overflow=auto;height=380;width=485></div>',event);\">
		<span id='namaBrg'></span></td>
	</tr>
	
	<tr>
		<td>Norma / Sat / Rot</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:150px;' id='norma_2' onkeyup='kalikannorma(2)' onkeypress='return angka_doang(event)' value='' /></td>
	</tr>
    <tr><td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['setahun']."&nbsp;<span id='satuan'></span></td><td>:</td><td><input type='text' class='myinputtextnumber' id='jmlh_2' disabled style='width:150px;' onkeypress='return angka_doang(event)' onkeyup='jumlahkan(2)' /></td></tr>
<tr><td>".$_SESSION['lang']['totalharga']."</td><td>:</td><td><input type='text' class='myinputtextnumber' id='totHarga' style='width:150px;' onkeypress='return false'  value='0' /></td></tr>        
<tr><td></td><td></td><td colspan=3>
<button class=mybutton id=btlTmbl2 name=btlTmbl2 onclick='saveBudget(2)'   >".$_SESSION['lang']['save']."</button></td></tr></table>
<input type=hidden id=prosesBr name=prosesBr value=insert_baru >";
//$frm[0].="</fieldset>";

$frm[1].="</fieldset>";
$frm[1].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
    <table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
			<td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tipeBudget']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kegiatan']."</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
			<td align=center>".$_SESSION['lang']['aruskas']."</td>
             <td align=center width=50px>".$_SESSION['lang']['rotasi']." / ".$_SESSION['lang']['tahun']."</td>
            <td align=center>".$_SESSION['lang']['volume']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['rp']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>Action</td>
            </tr>
            </thead><tbody id=containDataBrg>
		";
$frm[1].="</tbody></table></fieldset>";

$sOrgm="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget='TOOL' order by kodebudget asc";
$qOrgm=$owlPDO->query($sOrgm) or die(print " Gagal: ".PDOException::getMessage());
$qOrgm->setFetchMode(PDO::FETCH_ASSOC);
$optKdbdgtL="";
while($rOrgm=$qOrgm->fetch()){
    $optKdbdgtL.="<option value='".$rOrgm['kodebudget']."'>".$rOrgm['kodebudget']." [".$rOrgm['nama']."]</option>";
}
$frm[2].="<fieldset><legend>".$_SESSION['lang']['peralatan']."</legend>";
$frm[2].="<table cellspacing=1 border=0>
<tr>
	<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
	<td><select id='aruskasalat' style='width:155px;'>".$optaruskas."</select></td>
</tr>
<tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
<select id='kdBudgetL' style='width:153px;' disabled>".$optKdbdgtL."</select></td></tr>
<tr><td>".$_SESSION['lang']['kodebarang']."</td><td>:</td><td><input type='text' class='myinputtext' id='kdBarangL' style='width:150px;' onkeypress='return angka_doang(event)' />&nbsp;<img src='images/skyblue/zoom.png' style='position:relative;top:2px;' class=\"resicon\" title='".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."' onclick=\"searchBrgL('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."','<fieldset><legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>".$_SESSION['lang']['find']."&nbsp;<input type=text class=myinputtext id=nmBrgL><button class=mybutton onclick=findBrgL()>".$_SESSION['lang']['find']."</button></fieldset><div id=containerBarangL style=overflow=auto;height=380;width=485></div>',event);\">
    <span id='namaBrgL'></span></td>
	</tr>
	
	<tr>
		<td>Norma / Sat / Rot</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:150px;' id='norma_3' onkeyup='kalikannorma(3)' onkeypress='return angka_doang(event)' value='' /></td>
	</tr>
	
    <tr><td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['setahun']."</td><td>:</td><td><input type='text' class='myinputtextnumber' id='jmlh_3' style='width:150px;' disabled  onkeypress='return angka_doang(event)' onkeyup='jumlahkan(3)' />&nbsp;<span id='satuanL'></span></td></tr>
<tr><td>".$_SESSION['lang']['totalharga']."</td><td>:</td><td><input type='text' class='myinputtextnumber' id='totHargaL' style='width:150px;' onkeypress='return false'  value='0' /></td></tr>        
<tr><td></td><td></td><td colspan=3>
<button class=mybutton id=btlTmbl2 name=btlTmbl2 onclick='saveBudget(3)'   >".$_SESSION['lang']['save']."</button></td></tr></table>
<input type=hidden id=prosesBr name=prosesBr value=insert_baru >";

$frm[2].="</fieldset>";
$frm[2].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
    <table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
			<td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tipeBudget']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kegiatan']."</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
			<td align=center>".$_SESSION['lang']['aruskas']."</td>
             <td align=center width=50px>".$_SESSION['lang']['rotasi']." / ".$_SESSION['lang']['tahun']."</td>
            <td align=center>".$_SESSION['lang']['volume']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['rp']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>Action</td>
            </tr>
            </thead><tbody id=containDataTool>
		";
$frm[2].="</tbody></table></fieldset>";

$sOrgB="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget like '%KONTRAK%' order by nama asc";
$qOrgB=$owlPDO->query($sOrgB) or die(print " Gagal: ".PDOException::getMessage());
$qOrgB->setFetchMode(PDO::FETCH_ASSOC);
$optKdbdgt_B="";
while($rOrgB=$qOrgB->fetch()){
    $optKdbdgt_B.="<option value='".$rOrgB['kodebudget']."'>".$rOrgB['nama']."</option>";
}
$optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sJns="select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 and tipeakun='BIAYA' order by noakun asc";
$qJns=$owlPDO->query($sJns) or die(print " Gagal: ".PDOException::getMessage());
$qJns->setFetchMode(PDO::FETCH_ASSOC);
while($rJns=$qJns->fetch()){
    $optAkun.="<option value='".$rJns['noakun']."'>".$rJns['noakun']." - [".$rJns['namaakun']."]</option>";
}
$frm[3]="<fieldset><legend>".$_SESSION['lang']['kontrak']."</legend>";
$frm[3].="<table cellspacing=1 border=0>

<tr>
	<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
	<td><select id='aruskaskont' style='width:155px;'>".$optaruskas."</select></td>
</tr>
	<tr>
		<td>".$_SESSION['lang']['kodeanggaran']."</td>
		<td>:</td>
		<td><select id='kdBudgetK' style='width:155px;' disabled>".$optKdbdgt_B."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['volume']." (%)</td>
		<td>:</td>
		<td><input type='text' id='volpersen' onkeyup=gettotalvolume() class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' /></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['volume']." ".$_SESSION['lang']['total']."</td>
		<td>:</td>
		<td><input type='text' id='volKontrak' disabled class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' /></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['harga']."</td>
		<td>:</td>
		<td><input type='text' id='hargakontrak' onkeyup=gettotalvolume() class='myinputtextnumber' onkeypress='return tanpa_kutip(event)' style='width:150px;' /></td>
		</tr>
	<tr>
		<td>".$_SESSION['lang']['satuan']."</td>
		<td>:</td>
		<td><input type='text' disabled id='satKontrak' class='myinputtext' onkeypress='return tanpa_kutip(event)' style='width:150px;' /></td>
		</tr>
	<tr>
		<td>".$_SESSION['lang']['totalbiaya']." ".$_SESSION['lang']['setahun']."</td>
		<td>:</td>
		<td><input type='text' disabled class='myinputtextnumber' id='totBiayaK' style='width:150px;' onkeypress='return angka_doang(event)' value='0' /></td>
	</tr>
	<tr><td></td><td></td><td colspan=3>
		<button class=mybutton onclick=saveBudget(4) >".$_SESSION['lang']['save']."</button>
		<input type=hidden name=prosesOpt id=prosesOpt value=insert_operator />
		</td></tr>
	</table>";

$frm[3].="</fieldset>";
$frm[3].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
    <table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tipeBudget']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kegiatan']."</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
			<td align=center>".$_SESSION['lang']['aruskas']."</td>
             <td align=center width=50px>".$_SESSION['lang']['rotasi']." / ".$_SESSION['lang']['tahun']."</td>
            <td align=center>".$_SESSION['lang']['volume']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['rp']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>Action</td>
            </tr>
            </thead><tbody id=containDataLain>";
$frm[3].="</tbody></table></fieldset>";


$sOrgv="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget like '%VHC%' order by nama asc";
$qOrgv=$owlPDO->query($sOrgv) or die(print " Gagal: ".PDOException::getMessage());
$qOrgv->setFetchMode(PDO::FETCH_ASSOC);
$optKdbdgt_V="";
while($rOrgv=$qOrgv->fetch()){
    $optKdbdgt_V.="<option value='".$rOrgv['kodebudget']."'>".$rOrgv['nama']."</option>";
}
$optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sJns="select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 and tipeakun='BIAYA' order by noakun asc";
$qJns=$owlPDO->query($sJns) or die(print " Gagal: ".PDOException::getMessage());
$qJns->setFetchMode(PDO::FETCH_ASSOC);
while($rJns=$qJns->fetch())
{
    $optAkun.="<option value='".$rJns['noakun']."'>".$rJns['noakun']." - [".$rJns['namaakun']."]</option>";
}
$optVhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$oVhc = 

$frm[4]="<fieldset><legend>".$_SESSION['lang']['kndran']."</legend>";
$frm[4].="<table cellspacing=1 border=0>
<tr>
	<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
	<td><select id='aruskasvhc' style='width:155px;'>".$optaruskas."</select></td>
</tr>

<tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
<select id='kdBudgetV' style='width:155px;' disabled>".$optKdbdgt_V."</select></td></tr>
<tr><td>".$_SESSION['lang']['kodevhc']."</td><td>:</td><td><select id='kdVhc' style='width:155px;' onchange='ambil_biaya()'>".$optVhc."</select><img id='kdVhc' onclick=z.elSearch('kdVhc',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td></tr>
	<tr>
		<td>Norma / Sat / Rot</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:150px;' id='norma_5' onkeyup='kalikannorma(5)' onkeypress='return angka_doang(event)' value='' /></td>
	</tr>


<tr><td>".$_SESSION['lang']['jmlhJam']."</td><td>:</td><td><input type='text' class='myinputtextnumber' id='jmlhJam' style='width:150px;' onkeypress='return angka_doang(event)'   onkeyup='ambil_biaya()' /></td></tr>
<tr><td>".$_SESSION['lang']['satuan']."</td><td>:</td><td><input type='text' id='satVhc' class='myinputtext' disabled value='HM/KM' style='width:150px;' /></td></tr>
<tr><td>".$_SESSION['lang']['totalbiaya']."</td><td>:</td><td><input type='text' class='myinputtextnumber' id='totBiayaKend' style='width:150px;' onkeypress='return false' value=0 /></td></tr>        
<tr><td></td><td></td><td colspan=3>
<button class=mybutton onclick=saveBudget(5) >".$_SESSION['lang']['save']."</button>
</td></tr>
</table>";

$frm[4].="</fieldset>";
$frm[4].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
    <table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>         
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>
            <td align=center width=50px>".$_SESSION['lang']['tipeBudget']."</td>
            <td align=center width=50px>".$_SESSION['lang']['kodeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kegiatan']."</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
            <td align=center>".$_SESSION['lang']['aruskas']."</td>
           <td align=center>".$_SESSION['lang']['kodevhc']."</td>
            <td align=center>".$_SESSION['lang']['rp']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>Action</td>
            </tr>
            </thead><tbody id=containDataKend>
		";
$frm[4].="</tbody></table></fieldset>";

$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Okt","11"=>"Nov","12"=>"Des");
$frm[5]="<fieldset><legend>".$_SESSION['lang']['sebaran']."</legend>
    <table cellpadding=1 cellspacing=1 border=0 class=sortable><tr class=rowheader>";
    foreach($arrBln as $brsBulan =>$listBln){
		$frm[5].="<td align=center>".$listBln."</td>";
	}
       
$sNamaAkun58="select distinct noakun,namaakun  from ".$dbname.".keu_5akun order by namaakun asc";
$qNamaAkun58=$owlPDO->query($sNamaAkun58) or die(print " Gagal: ".PDOException::getMessage());
$qNamaAkun58->setFetchMode(PDO::FETCH_ASSOC);
while($rNamaAkun58=  $qNamaAkun58->fetch()){
    $namaAkun58[$rNamaAkun58['noakun']]=$rNamaAkun58['namaakun'];
}

$optNoakunData58="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOptNoakun58="select distinct noakun from ".$dbname.".bgt_budget where tipebudget='ESTATE' and kodebudget!='UMUM' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by noakun asc";
$qOptNoakun58=$owlPDO->query($sOptNoakun58) or die(print " Gagal: ".PDOException::getMessage());
$qOptNoakun58->setFetchMode(PDO::FETCH_ASSOC);
while($rOptNoakun58=$qOptNoakun58->fetch()){
    $optNoakunData58.="<option value='".$rOptNoakun58['noakun']."'>".$rOptNoakun58['noakun']."-".$namaAkun58[$rOptNoakun58['noakun']]."</option>";
}

$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$opt58="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOptNoakun58="select distinct kegiatan from ".$dbname.".bgt_budget where tipebudget='ESTATE' and kodebudget!='UMUM' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by kegiatan asc";
$qOptNoakun58=$owlPDO->query($sOptNoakun58) or die(print " Gagal: ".PDOException::getMessage());
$qOptNoakun58->setFetchMode(PDO::FETCH_ASSOC);
while($rOptNoakun58=$qOptNoakun58->fetch()){
    $opt58.="<option value='".$rOptNoakun58['kegiatan']."'>".$rOptNoakun58['kegiatan']."-".$nmkeg[$rOptNoakun58['kegiatan']]."</option>";
}

$arropt99=array(''=>$_SESSION['lang']['all'],'1'=>'Yes','2'=>'No');
foreach($arropt99 as $key => $val){
	@$opt99.="<option value='".$key."'>".$val."</option>";
}

$sAfd="select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$qAfd=$owlPDO->query($sAfd) or die(print " Gagal: ".PDOException::getMessage());
$qAfd->setFetchMode(PDO::FETCH_ASSOC);
$optAfdeling="<option value=''>".$_SESSION['lang']['all']."</option>";
while($rAfd=  $qAfd->fetch()){
    $optAfdeling.="<option value='".$rAfd['kodeorganisasi']."'>".$rAfd['kodeorganisasi']."</option>";
}
$frm[5].="<td>Action</td></tr>
    <tr class=rowcontent>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss1 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss2 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss3 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss4 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss5 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss6 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss7 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss8 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss9 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss10 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss11 value=1></td>
    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss12 value=1></td>
    <td align=center><img src=images/clear.png onclick=bersihkanDonk() class='resicon' style='position:relative;top:2px;cursor:pointer' title='bersihkan'></td>
    </tr>
    </table> <span>Isi persen ".$_SESSION['lang']['sebaran']." diatas kemudian click list kegiatan dibawah</span><hr>";


    $frm[5].="<fieldset style=float:left><legend>Find</legend><table><tr class=rowcontent>
              <td>".$_SESSION['lang']['divisi']."</td><td><select id=AfdSebaran onchange='loadDetailTotal()'>".$optAfdeling."</select></td>
              <td>".$_SESSION['lang']['kodeblok']."</td><td><select id=kdblokSebaran onchange='loadDetailTotal()'>".$optBlok."</select><img id='kdblokSebaran' onclick=z.elSearch('kdblokSebaran',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			  
			  </td>
			  <td>".$_SESSION['lang']['noakun']."</td><td><select style=width:150px  id=kdNoakunData onchange='loadDetailTotal()'>".$optNoakunData58."</select><img id='kdNoakunData' onclick=z.elSearch('kdNoakunData',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
			  
              <td>".$_SESSION['lang']['kegiatan']."</td><td>
			  <input style=width:150px type='text' id='kdKegiatanData' onkeypress='enterkey(event,loadDetailTotal)' class='myinputtext' />
			  </td>
			  
			  <td>".$_SESSION['lang']['sebaran']."</td><td><select style=width:100px  id=kdsebaranData onchange='loadDetailTotal()'>".$opt99."</select></td>
			  
			  <td>Jumlah Baris</td><td><input style=width:50px value='50' type='text' id='jlhbaris' onkeypress='enterkey(event,loadDetailTotal)' class='myinputtextnumber' /></td>
			  
              <td style=display:none>Goto Page</td><td  style=display:none id='pagingDrop'>&nbsp;<select id='pageSebaran' onchange='loadDetailTotal()'><option value=''></option></select><span id=awalPageSebaran></span> &nbsp;".$_SESSION['lang']['dari']." &nbsp;<span id=totalPageSebaran></span></td>
			  <td colspan=5><button class=mybutton onclick=loadDetailTotal() >".$_SESSION['lang']['preview']."</button></td></tr>
              </table>
			  </fieldset><div style=clear:both></div><hr>";
    
   $frm[5].="<div style=clear:both></div>
			<div id='detailDataSebaran'style=overflow:auto;min-height:300px;>
			
			<div style=clear:both></div>

			<div id='both_report'>
				<div id='head_tableboth' align=right>
					<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='containDataTotal' table='sortable' >
						<img title='Full Screen' class='resicon' src='images/full-screen.png'>
					</a>
					<a class='fixheadbtn mybutton' table='sortable' idbothbody='containDataTotal' shown='0' >
						<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
					</a>
				</div>
				<div id='containDataTotal' style='overflow:auto;height:380px'; ></div>
			</div>
			
			";
			
$frm[5].="</div></fieldset>";

$hfrm[0]=$_SESSION['lang']['sdm'];
$hfrm[1]=$_SESSION['lang']['material'];
$hfrm[2]=$_SESSION['lang']['peralatan'];
$hfrm[3]=$_SESSION['lang']['kontrak'];
$hfrm[4]=$_SESSION['lang']['kndran'];
$hfrm[5]=$_SESSION['lang']['sebaran'];
drawTab('FRM',$hfrm,$frm,150,'100%');

CLOSE_BOX();
echo"</div>";
echo close_body();
?>
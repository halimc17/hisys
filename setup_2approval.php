<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/approval.js?v=<?php echo time(); ?>'></script>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('setup_2approval').'</span><br>');

$optCrOrg = $optCrkar =$optCrkaruser = $optCrjnspstj = $optCrapp ="<option value=''>".$_SESSION['lang']['all']."</option>";
$optdepcr = $optjabcr = $opttipecr = $optjabcr = $optgolcr ="<option value=''>".$_SESSION['lang']['all']."</option>";
$optjab=$opttipe=$optdep=$optkar=$optgol=$optOrg=$optjnspstj=$optCrkarlama=$optCrkarbaru="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

##KODE ORGANISASI
$str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by kodeorganisasi");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
	$optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$optCrOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

##DATA KARYAWAN
$str=$owlPDO->query("select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan not in ('4','5') order by namakaryawan");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
    $optCrkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
    $optCrkarbaru.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
}

$str=$owlPDO->query("select distinct karyawaniduser from ".$dbname.".setup_approval 
      where karyawaniduser!='' order by karyawaniduser");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
	$optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar->karyawaniduser."'");
    $optCrkaruser.="<option value='".$bar->karyawaniduser."'>".$optNmKry[$bar->karyawaniduser]."</option>";
}

##DATA KARYAWAN replace
$str=$owlPDO->query("select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
      where tipekaryawan in ('0','1','6','7','8','9','10','11','12','13') order by namakaryawan");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    //$optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
    $optCrkarlama.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->lokasitugas."</option>";
}

##JENIS PERSETUJUAN
$str=$owlPDO->query("select distinct jenis from ".$dbname.".setup_jenisapproval where status='1' order by jenis asc");
$optNmJns = makeOption($dbname,'setup_jenisapproval','jenis,nama');
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $optjnspstj.="<option value='".$bar->jenis."'>".$bar->jenis." - ".$optNmJns[$bar->jenis]."</option>";
    $optCrjnspstj.="<option value='".$bar->jenis."'>".$bar->jenis." - ".$optNmJns[$bar->jenis]."</option>";
}

##LEVEL APPROVAL
$optapp="";
for($i=1;$i<=10;$i++){
	$optapp.="<option value='".$i."'>Level ".$i."</option>";
	$optCrapp.="<option value='".$i."'>Level ".$i."</option>";
}

##DEPARTEMEN
$str=$owlPDO->query("select kode,nama from ".$dbname.".sdm_5departemen order by nama asc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $optdep.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
    $optdepcr.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
}

##TIPE KARYAWAN
$str=$owlPDO->query("select id,tipe from ".$dbname.".sdm_5tipekaryawan order by id asc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $opttipe.="<option value='".$bar->id."'>".$bar->tipe."</option>";
    $opttipecr.="<option value='".$bar->id."'>".$bar->tipe."</option>";
}

##GOLONGAN KARYAWAN
// $arrgol = array('1','2','3','4','5','6','7');
// $where ='';
// foreach ($arrgol as $key => $value) {
// 	if($key == '0'){
// 		$where.=" namagolongan LIKE '".$value."%'";
// 	}else{
// 		$where.=" OR namagolongan LIKE '%".$value."%'";
// 	}
// }
$str=$owlPDO->query("select distinct left(namagolongan,1) as namagolongan from ".$dbname.".sdm_5golongan where  aktif='1' and kodegolongan != '32' order by kodegolongan desc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
		$optgol.="<option value='".$bar->namagolongan."'>".$bar->namagolongan." </option>";
		$optgolcr.="<option value='".$bar->namagolongan."'>".$bar->namagolongan."</option>";
}

##JABATAN
$str=$owlPDO->query("select kodejabatan,namajabatan from ".$dbname.".sdm_5jabatan order by kodejabatan asc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $optjab.="<option value='".$bar->kodejabatan."'>".$bar->kodejabatan." - ".$bar->namajabatan."</option>";
    $optjabcr.="<option value='".$bar->kodejabatan."'>".$bar->kodejabatan." - ".$bar->namajabatan."</option>";
}

##FORM
echo"<table border=0 cellpadding=1 cellspacing=1 hidden>
		<tr>
			<td colspan=1 valign=top style=min-width:400px;>
				<fieldset style=height:auto;float:left;>
					<legend><b>".$_SESSION['lang']['form']." ".$_SESSION['lang']['user']."</b></legend>
					<table border=0 style='display: inline-block;vertical-align:top'>
						<tr>
							<td width=150px>".$_SESSION['lang']['kodeorg']."</td>
							<td>:</td>
							<td>
								<select style=width:200px onchange=getkary() id=kodeorg>".$optOrg."</select>
								<img id='kodeorg' onclick=z.elSearch('kodeorg',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kodegolongan']."</td>
							<td>:</td>
							<td>
								<select style=width:200px id=golongan onchange=getkarygol()>".$optgol."</select>
								<img id='golongan' onclick=z.elSearch('golongan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['namakaryawan']." ".$_SESSION['lang']['user']."</td>
							<td>:</td>
							<td>
								<select style=width:200px id=karyawaniduser></select>
								<img id='karyawaniduser' onclick=z.elSearch('karyawaniduser',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>

			<td valign=top style=width:300px>
				<fieldset style=height:100%><legend><b>Replace</b></legend>
				<table style='display: inline-block;vertical-align:top'>	
					<tr>
						<td>".$_SESSION['lang']['namakaryawan']." Lama</td>
						<td>:</td>
						<td>
							<select style=width:150px id=karyawanidrep1>".$optCrkarlama."</select>
							<img id='karyawanidrep1' onclick=z.elSearch('karyawanidrep1',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['namakaryawan']." Baru</td>
						<td>:</td>
						<td>
							<select style=width:150px id=karyawanidrep2>".$optCrkarbaru."</select>
							<img id='karyawanidrep2' onclick=z.elSearch('karyawanidrep2',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=simpanreplace()>".$_SESSION['lang']['save']."</button>
						</td>
					</tr>
				</table>
				</fieldset>
				
				
			</td> 
		</tr>
		<tr>
			<td valign=top>
				<fieldset style=float:left;min-height:208px>
				<legend><b>".$_SESSION['lang']['form']." User Penyetuju</b></legend>
				<table border=0 style='display: inline-block;vertical-align:top'>
					<div style=display:none>
						<input id=kodeorgold>
						<input id=jenispersetujuanold>
						<input id=levelold>
						<input id=departemenold>
						<input id=jabatanold>
						<input id=tipekaryawanold>
						<input id=golonganold>
						<input id=karyawanidold>
						<input id=karyawaniduserold>
						<input id=tipeold>
					</div>
						
					<tr>
						<td  width=150px>".$_SESSION['lang']['jenispersetujuan']."</td> 
						<td>:</td>
						<td>
							<select style=width:200px onchange=getkary() id=jenispersetujuan>".$optjnspstj."</select>
							<img id='jenispersetujuan' onclick=z.elSearch('jenispersetujuan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['level']."</td>
						<td>:</td>
						<td>
							<select id=level onchange=getkary()>".$optapp."</select>
							<img id='level' onclick=z.elSearch('level',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['departemen']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=departemen onchange=getkary()>".$optdep."</select>
							<img id='departemen' onclick=z.elSearch('departemen',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jabatan']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=jabatan onchange=getkary()>".$optjab."</select>
							<img id='jabatan' onclick=z.elSearch('jabatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tipekaryawan']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=tipekaryawan onchange=getkary()>".$opttipe."</select>
							<img id='tipekaryawan' onclick=z.elSearch('tipekaryawan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['namakaryawan']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=karyawanid>".$optkar."</select>
							<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tipe']." Global / Non-Global</td> 
						<td>:</td>
						<td><input type=checkbox id=tipe></td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<input type=hidden id=method value='simpan'>
							<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
							<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
						</td>
					</tr>
				</table>
				</fieldset><br>
				<fieldset>
					<center><button class=mybutton onclick=tampilkanformdelete()>Tampilkan Form Detele All</button></center>
				</fieldset>
			</td>
			
			<td valign=top align=center width=350px>
				<fieldset style=min-height:257px><legend><b>Copy</b></legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['kodeorg']." (".$_SESSION['lang']['dari'].")</td>
						<td>:</td>
						<td>
							<select style=width:150px id=kodeorgcopy1>".$optOrg."</select>
							<img id='kodeorgcopy1' onclick=z.elSearch('kodeorgcopy1',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jenispersetujuan']." (".$_SESSION['lang']['dari'].")</td> 
						<td>:</td>
						<td>
							<select style=width:150px id=jenispersetujuancopy>".$optjnspstj."</select>
							<img id='jenispersetujuancopy' onclick=z.elSearch('jenispersetujuancopy',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['departemen']." (".$_SESSION['lang']['dari'].")</td>
						<td>:</td>
						<td>
							<select style=width:150px id=departemencopy>".$optdep."</select>
							<img id='departemencopy' onclick=z.elSearch('departemencopy',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kodegolongan']." (".$_SESSION['lang']['dari'].")</td>
						<td>:</td>
						<td>
							<select style=width:150px id=golongancopy>".$optgol."</select>
							<img id='golongancopy' onclick=z.elSearch('golongancopy',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					
					
					<tr>
						<td>".$_SESSION['lang']['kodeorg']." (".$_SESSION['lang']['tujuan'].")</td>
						<td>:</td>
						<td>
							<select style=width:150px id=kodeorgcopy2>".$optOrg."</select>
							<img id='kodeorgcopy2' onclick=z.elSearch('kodeorgcopy2',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jenispersetujuan']." (".$_SESSION['lang']['tujuan'].")</td> 
						<td>:</td>
						<td>
							<select style=width:150px id=jenispersetujuancopy2>".$optjnspstj."</select>
							<img id='jenispersetujuancopy2' onclick=z.elSearch('jenispersetujuancopy2',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['departemen']." (".$_SESSION['lang']['tujuan'].")</td>
						<td>:</td>
						<td>
							<select style=width:150px id=departemencopy2>".$optdep."</select>
							<img id='departemencopy2' onclick=z.elSearch('departemencopy2',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					
					<tr>
						<td>".$_SESSION['lang']['kodegolongan']." (".$_SESSION['lang']['tujuan'].")</td>
						<td>:</td>
						<td>
							<select style=width:150px id=golongancopy2>".$optgol."</select>
							<img id='golongancopy2' onclick=z.elSearch('golongancopy2',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=simpancopy()>".$_SESSION['lang']['save']."</button>
						</td>
					</tr>
				</table>
				</fieldset>
				<!--
				<fieldset><legend><b>Delete All</b></legend>
					<button class=mybutton onclick=tampilkanformdelete()>".$_SESSION['lang']['preview']."</button>
				</fieldset>
				-->
				
			</td>";
			
			/* echo"<td  valign=top>
				<fieldset style=float:left><legend><b>Delete</b></legend>
				<table style='display: inline-block;vertical-align:top'>	
					<tr>
						<td>".$_SESSION['lang']['kodeorg']."</td>
						<td>:</td>
						<td>
							<select style=width:150px id=cr2kodeorg>".$optCrOrg."</select>
						</td>
					</tr>
					<tr>
						<td style='padding-left:10px;'>".$_SESSION['lang']['level']."</td>
						<td>:</td>
						<td>
							<select id=cr2level style=width:150px >".$optCrapp."</select>
						</td>
					</tr>
					<tr>
						<td style='padding-left:10px;'>".$_SESSION['lang']['departemen']."</td>
						<td>:</td>
						<td>
							<select id=cr2departemen style=width:150px >".$optdepcr."</select>
							<img id='cr2departemen' onclick=z.elSearch('cr2departemen',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td style='padding-left:10px;'>".$_SESSION['lang']['jabatan']."</td>
						<td>:</td>
						<td>
							<select id=cr2jabatan style=width:150px >".$optjabcr."</select>
							<img id='cr2jabatan' onclick=z.elSearch('cr2jabatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jenispersetujuan']."</td> 
						<td>:</td>
						<td>
							<select style=width:150px id=cr2jenispersetujuan >".$optCrjnspstj."</select>
						</td>
					</tr>
					<tr>
						<td style='padding-left:10px;'>".$_SESSION['lang']['namakaryawan']."</td>
						<td>:</td>
						<td>
							<select style=width:150px id=cr2karyawanid >".$optCrkar."</select>
							<img id='cr2karyawanid' onclick=z.elSearch('cr2karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td style='padding-left:10px;'>".$_SESSION['lang']['tipekaryawan']."</td>
						<td>:</td>
						<td>
							<select id=cr2tipekaryawan style=width:150px >".$opttipecr."</select>
							<img id='cr2tipekaryawan' onclick=z.elSearch('cr2tipekaryawan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td style='padding-left:10px;'>".$_SESSION['lang']['kodegolongan']."</td>
						<td>:</td>
						<td>
							<select id=cr2golongan style=width:150px >".$optgolcr."</select>
							<img id='cr2golongan' onclick=z.elSearch('cr2golongan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=formdelete('event')>".$_SESSION['lang']['preview']."</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>";
		 */
		echo"</tr>
		<tr>
			<td colspan=3 style=width:300px>
				<fieldset style=float:right>
				<legend><b>".$_SESSION['lang']['keterangan']."</b></legend>
				<table>
					<tr>
						<td valign=top>Approval PR</td>
						<td valign=top>:</td>
						<td valign=top>Jika kode dept requester sama dengan kode dept di setup maka akan di ambil nama - nama karyawan pada dept tersebut, jika tidak ada yg sama maka akan di ambil nama - nama karyawan yang kode dept kosong (Default)</td>
					</tr>
					<!--<tr>
						<td valign=top>".$_SESSION['lang']['level']."</td>
						<td valign=top>:</td>
						<td valign=top>Level persetujuan, isi hanya (Level 1 s/d Level 10)</td>
					</tr>
					<tr>
						<td valign=top>Contoh</td>
						<td valign=top>:</td>
						<td valign=top>".$_SESSION['lang']['kodeorg']." : BENGKAYANG MILL, ".$_SESSION['lang']['jenispersetujuan']." : PR, ".$_SESSION['lang']['level']." : Level 1, Karyawan : Hermanto <br> Berarti : Hermanto hanya dapat menyetujui PR jika Hermanto berada di level ke-1 dan untuk unit BENGKAYANG MILL </td>
					</tr>-->
				</table>
				</fieldset>
				
				</td>
		</tr>
	
	</table>

</fieldset>";
echo"
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top;padding-bottom:10px;'>
		<tr>
			<td>
				<fieldset style=float:left><legend><b>".$_SESSION['lang']['find']."</b></legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['kodeorg']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crkodeorg onchange='loaddata()'>".$optCrOrg."</select>
						</td>
						
						<td style='padding-left:10px;'>".$_SESSION['lang']['level']."</td>
						<td>:</td>
						<td>
							<select id=crlevel style=width:200px onchange='loaddata()'>".$optCrapp."</select>
						</td>

						<td style='padding-left:10px;'>".$_SESSION['lang']['departemen']."</td>
						<td>:</td>
						<td>
							<select id=crdepartemen style=width:150px onchange='loaddata()'>".$optdepcr."</select>
							<img id='crdepartemen' onclick=z.elSearch('crdepartemen',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>

						<td style='padding-left:10px;'>".$_SESSION['lang']['jabatan']."</td>
						<td>:</td>
						<td>
							<select id=crjabatan style=width:150px onchange='loaddata()'>".$optjabcr."</select>
							<img id='crjabatan' onclick=z.elSearch('crjabatan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jenispersetujuan']."</td> 
						<td>:</td>
						<td>
							<select style=width:200px id=crjenispersetujuan onchange='loaddata()'>".$optCrjnspstj."</select>
						</td>
						
						<td style='padding-left:10px;'>".$_SESSION['lang']['namakaryawan']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crkaryawanid onchange='loaddata()'>".$optCrkar."</select>
							<img id='crkaryawanid' onclick=z.elSearch('crkaryawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>

						<td style='padding-left:10px;'>".$_SESSION['lang']['tipekaryawan']."</td>
						<td>:</td>
						<td>
							<select id=crtipekaryawan style=width:150px onchange='loaddata()'>".$opttipecr."</select>
							<img id='crtipekaryawan' onclick=z.elSearch('crtipekaryawan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>

						<td style='padding-left:10px;'>".$_SESSION['lang']['kodegolongan']."</td>
						<td>:</td>
						<td>
							<select id=crgolongan style=width:150px onchange='loaddata()'>".$optgolcr."</select>
							<img id='crgolongan' onclick=z.elSearch('crgolongan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>

					</tr>
					<tr>
						<td>".$_SESSION['lang']['namakaryawan']."  ".$_SESSION['lang']['user']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crkaryawaniduser onchange='loaddata()'>".$optCrkaruser."</select>
							<img id='crkaryawaniduser' onclick=z.elSearch('crkaryawaniduser',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
						</td>
						<td></td>
						<td></td>
						<td colspan=5><button class=mybutton onclick='batalcari();loaddata()'>".$_SESSION['lang']['resetvariableoutput']."</button></td>
					</tr>
					<tr style=height:25px;background-color:cyan;display:none>
						<td style='text-align:center' colspan=60>
							<button class=mybutton onclick='batalcari();loaddata()'>".$_SESSION['lang']['resetvariableoutput']."</button>
							<button class=mybutton onclick=tampilkanformdelete()>Tampilkan Form Detele All</button>
						</td>
					</tr>
				 </table>
				 </fieldset>
			</td> 
		</tr>
	</table>
<div class='table-scroll'>
	<table class=sortable cellspacing=1 cellpadding=3 border=0>
		<thead>
		<tr class=rowheader style='font-weight:bold'>
			<th style='text-align:center;width:70px'>".$_SESSION['lang']['kodeorg']."</th>
			<th style='text-align:center;display:none'>".$_SESSION['lang']['namaorganisasi']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['jenispersetujuan']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['level']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['departemen']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['jabatan']."</th>
			<th style='text-align:center;width:70px'>".$_SESSION['lang']['tipekaryawan']."</th>
			<th style='text-align:center;width:50px'>".$_SESSION['lang']['kodegolongan']."<br>".$_SESSION['lang']['user']."</th>
			<th style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."<br>".$_SESSION['lang']['user']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['tipe']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['createby']."</th>
			<th style='text-align:center;width:70px'>".$_SESSION['lang']['createtime']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['updatedby']."</th>
			<th style='text-align:center;width:70px'>".$_SESSION['lang']['updatedtime']."</th>
			<th style='text-align:center' colspan=3>".$_SESSION['lang']['action']."</th></tr>
		 </thead>
		 <tbody id=container>
			<script>loaddata()</script>
		 </tbody>
		 <tfoot id=footData></tfoot>
	
	</table>
</div>";
CLOSE_BOX();
echo close_body();
?>
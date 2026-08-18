<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_permintaan_perbaikan').'</span>');
//print_r($_SESSION['temp']);
?>


<script language=javascript src='js/pabrik_permintaan_perbaikan.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<!--deklarasi untuk option-->
<?php


#



$optKaryawan=$optKaryawanpemohon=$optTuntas=$optStation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

##untuk pilihan pabrik 	
$optPabrik='';
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optPabrik.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi "
        . " where induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('STATION','MAINTENANCE')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optStation.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' 
                    and a.tipekaryawan not in ('0','7','8') and
			(b.namajabatan like '%mecha%' or b.namajabatan like '%process%' or b.namajabatan like '%Krani%' or b.namajabatan like '%Asisten%' 
                        or b.namajabatan like '%maintenance%' or b.namajabatan like '%elect%' or b.namajabatan like '%elektri%' or b.namajabatan like '%mekanik%' or b.namajabatan like '%Mandor Pabrikasi%' or b.namajabatan like '%Operator Bubut%' or subbagian='".$_SESSION['empl']['lokasitugas']."10' or subbagian='".$_SESSION['empl']['lokasitugas']."17')
                        ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optKaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." [".$bar['nik']."]</option>";
}

$optTuntas.="<option value='Rencana'>Rencana</option>";
$optTuntas.="<option value='Lanjut'>Lanjut</option>";
$optTuntas.="<option value='Selesai'>Selesai</option>";
$optTuntas.="<option value='Tunda'>Tunda</option>";

#untuk nama pemohon dibuat jadi opt
// $str="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
		// left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		// where a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and 
			// (b.namajabatan like '%Maintenance%' or b.namajabatan like '%MEKANIK%') ";
	$str="select a.karyawanid,a.namakaryawan,a.nik,a.lokasitugas from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where (a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' or a.lokasitugas like '%HO') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') order by a.namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optKaryawanpemohon.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." [".$bar['nik']."] [".$bar['lokasitugas']."]</option>";
}

#buat pilihan status pemohon
$optStPemohon="<option value='R'>Manager</option>";
$optStPemohon.="<option value='A'>Asisten</option>";
$optStPemohon.="<option value='P'>Processing</option>";
$optStPemohon.="<option value='M'>Maintenance</option>";
$optStPemohon.="<option value='L'>Luar</option>";

#buat tipe perbaikan
//8. Type Perbaikan ( default value = Prev. Maintenance, Kalibrasi, Project, Pabrikasi )
$optPerbaikan="<option value='prev'>Preventive Maintenance</option>";
$optPerbaikan.="<option value='mayor'>Mayor Maintenance</option>";
// $optPerbaikan.="<option value='kalibrasi'>Kalibrasi</option>";
// $optPerbaikan.="<option value='project'>Project</option>";
// $optPerbaikan.="<option value='pabrikasi'>Pabrikasi</option>";
$optPerbaikan.="<option value='corrective'>Corrective Maintenance</option>";
// $optPerbaikan.="<option value='service'>Service</option>";
#shift
$optShift='';
for($i=1;$i<=3;$i++)
{
    $optShift.="<option value='".$i."'>".$i."</option>";
}

#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}

$optKondisi="<option value='normal'>Normal</option>";
$optKondisi.="<option value='perbaikan'>Perlu Perbaikan</option>";
$optKondisi.="<option value='rusak'>Rusak</option>";

#default mesin
$optMesin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	

//EDT=>Emergency Downtime,SDT=>Sequential Downtime,CDT=>"Commercial Downtime"
$nmdownst=array('EDT'=>'EDT - Breakdown','SDT'=>'SDT - Non Breakdown','CDT'=>'-');
$optmaninten="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arragama=getEnum($dbname,'pabrik_rawatmesinht','downstatus');
foreach($arragama as $kei=>$fal){
    $optmaninten.="<option value='".$kei."'>".$nmdownst[$fal]."</option>";
}	




$optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//persetujuan1
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSMAINTENANCE' and level=1 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan2
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSMAINTENANCE' and level=2 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper2.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan3
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSMAINTENANCE' and level=3 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper3.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}



?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList();loadData(0);>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo"
				<table>
					<tr>
						<td>".$_SESSION['lang']['notransaksi']."</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=schNodok /></td>
						
						<td>".$_SESSION['lang']['downstatus']."</td>
						<td>:</td>
						<td><select id=schdwnStat  style=\"width:150px;\">".$optmaninten."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=schTgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10  readonly/></td>
						<td>".$_SESSION['lang']['statusketuntasan']."</td>
						<td>:</td>	
						<td><select id=schstatusKetuntasan style=\"width:150px;\">'".$optTuntas."'</select></td>		
					</tr>
					<tr>
						<td>".$_SESSION['lang']['station']."</td>
						<td>:</td>
						<td><select id=schstation style=\"width:150px;\">'".$optStation."'</select></td>
					</tr>
					<tr>
						<td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
					</tr>
				</table>
			";
echo"</fieldset></td>
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>

<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php echo"
<div id=listData style=display:block>";//buka list data
OPEN_BOX();
	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=contain  style=display:block> 
                    <script>loadData(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

//<input type=text id=namaPemohon size=50  class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\">
//<td><select id=pabrik onchange=get_isi(this.options[this.selectedIndex].value,this.options[this.selectedIndex].text) style=\"width:150px;\">'".$optOrg."'</select></td>

echo "<div id=headher style=display:none>";//buka diff
OPEN_BOX();//<td><select id=kdorg disabled style=\"width:150px;\"><option  value='".$kdor."'>".$nmor."</option></select></td>
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0 cellspacing=1 cellpadding=1>
<tr>
	<td valign=top>
		<fieldset style=height:250px>
				<legend>".$_SESSION['lang']['formpermintaan']."</legend>
				<table cellpadding=1 cellspacing=1 border=0>
					<tr>
					<td>".$_SESSION['lang']['nodok']."</td>
					<td>:</td>		
					<td><input type=text id=nodok size=20 disabled class=myinputtext style=\"width:150px;\"></td>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>		
					<td><select id=pabrik disabled style=\"width:150px;\">'".$optPabrik."'</select></td>	
					</tr>
					<tr>
					<td>".$_SESSION['lang']['tanggal']." Order</td>
					<td>:</td>
					<td><input type=text onchange=getNodok() class=myinputtext id=tglOrder onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
					<select id=jmOrder>".$jm."</select>:<select id=mnOrder>".$mnt."</select></td>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>		
					<td><select id=station onchange=getMesin() style=\"width:150px;\">'".$optStation."'</select></td>
					</tr>
					<tr>
					<td>".$_SESSION['lang']['namapemohon']."</td>
					<td>:</td>	
					<td><select id=namaPemohon style=\"width:150px;\">'".$optKaryawanpemohon."'</select></td>
						
					
					
					<td>".$_SESSION['lang']['mesin']."</td>
					<td>:</td>		
					<td>
						<select id=mesin style=\"width:150px;\" >'".$optMesin."'</select>
					 <img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmbllistdatalalu class=resicon onclick=listdatalalu('".$_SESSION['lang']['find']."',event)>
					</td>
					
					</tr>
					<tr>
					<td>".$_SESSION['lang']['statuspemohon']."</td>
					<td>:</td>		
					<td><select id=statusPemohon  style=\"width:150px;\">".$optStPemohon."</select></td>
					
					<td>".$_SESSION['lang']['shift']."</td>
					<td>:</td>		
					<td><select id=shift  style=\"width:150px;\">".$optShift."</select></td>	
					</tr>
					<tr>
					<td  valign=top>".$_SESSION['lang']['downstatus']."</td>
					<td  valign=top>:</td>		
					<td  valign=top><select id=dwnStat  style=\"width:150px;\">".$optmaninten."</select></td>	
					<td>".$_SESSION['lang']['tipeperbaikan']."</td>
						<td>:</td>		
						<td><select id=tipePerbaikan  style=\"width:150px;\">".$optPerbaikan."</select></td>
					</tr>
					
					<tr>
						<td  valign=top>".$_SESSION['lang']['uraiankerusakan']."</td>
					<td  valign=top>:</td>
					<td  valign=top><textarea onkeypress=\"return tanpa_kutip(event)\" id=uraianKerusakan style=\"width:150px;\" rows=5></textarea></td>
					
						
					</tr>
					
				</table>
		</fieldset>
	</td>
	<td valign=top>
		<fieldset>
		  <legend>".$_SESSION['lang']['approve']."</legend>
		  <table>
		  
		  	<tr>
				<td>Persetujuan 1</td> 
				<td>:</td>
				<td><select id=persetujuan1 style='width:150px'>".$optper1."</select></td>
			</tr>
		   	<tr>
			    <td>Persetujuan 2</td> 
			    <td> : </td>
				<td><select id=persetujuan2 style='width:150px'>".$optper2."</select></td>
			</tr>
			<tr>	
			    <td>Persetujuan 3</td>
				<td> : </td>
				<td><select id=persetujuan3 style='width:150px'>".$optper3."</select></td>					 
			</tr>
			<tr style='display:none;'>	
			    <td>Direksi</td>
				<td> : </td>
				<td><select id=persetujuan4 style='width:150px'>".$optper4."</select></td>					 
			</tr>
		   </table>
		</fieldset>
	</td>
	
</tr>
</table>

	
	<table>
	<tr>
	<td>
	
			<button id=savehead class=mybutton onclick=saveHeader()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancelHead()>".$_SESSION['lang']['cancel']."</button>
			<button id=savehead class=mybutton onclick=add_new_data()>".$_SESSION['lang']['baru']."</button>
			
		<input type=hidden id=method value='insert'>
		</td>
	</tr>
	
	
		
	
	
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";




echo close_body();			
?>
    

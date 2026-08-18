<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript1.2 src='js/sdm_sop.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php
OPEN_BOX('','<span class=judul>'.getMenu('sdm_sop').'</span>');
$optjabatan=$optdept=$optper1=$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
}

$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='SOP' and level=1 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

$str="select kode,nama from ".$dbname.".sdm_5departemen where aktif=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optdept.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
}

$str="select kodejabatan,namajabatan from ".$dbname.".sdm_5jabatan where aktif=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optjabatan.="<option value=".$bar['kodejabatan'].">".$bar['kodejabatan']." - ".$bar['namajabatan']."</option>";
}

?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php

echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	echo"
	<tr>
            <td>".$_SESSION['lang']['tahun']."</td>
            <td>:</td>
            <td><select id=thnsch style=\"width:85px;\">'".$optthnsch."'</select></td>
	</tr>
            <td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
        </tr>
	";
        echo "</table>";
		
	
echo"</fieldset></td>";

echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>



<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 

echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    <fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:50%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['nodok']."</td>
					<td  align=center>No. Revisi</td>
                    <td  align=center>Tanggal Efektif</td>
                    <td  align=center>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
                
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

echo "<div id=header style=display:none>";//buka diff  style=display:none
OPEN_BOX();// 
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td><input type=text id=notransaksi   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
		
		<td>Disiapkan</td>
		<td>:</td>
		<td><select id=disiapkan style='width:150px;'>".$optkaryawan."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' class='myinputtext' id='tanggaldisiapkan' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\"></td> 
		
		
    </tr> 
	 <tr>
        <td>Revisi</td>
        <td>:</td>
        <td><input type=text id=norevisi   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
		
		<td>".$_SESSION['lang']['diperiksa']."</td>
		<td>:</td>
		<td><select id=diperiksa style='width:150px;'>".$optper1."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' class='myinputtext' id='tanggaldiperiksa' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\"></td> 
		
		
    </tr> 
	<tr>
		<td>".$_SESSION['lang']['tanggalmulai']."</td>
        <td>:</td>
        <td><input type='text' class='myinputtext' id='tanggalefektif' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\"></td> 
		
		<td>Disahkan</td>
		<td>:</td>
		<td><select id=disahkan style='width:150px;'>".$optkaryawan."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' class='myinputtext' id='tanggaldisahkan' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\"></td> 
	</tr>
	<tr>
		<td>".$_SESSION['lang']['departemen']."</td>
		<td>:</td>
		<td>
			<select id=departemen style='width:150px;'>".$optdept."</select>
			<img id=departemen onclick=z.elSearch('departemen',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
		</td>

		<td>".$_SESSION['lang']['jabatan']."</td>
		<td>:</td>
		<td>
			<select id=jabatan style='width:150px;'>".$optjabatan."</select>
			<img id=jabatan onclick=z.elSearch('jabatan',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
		</td>
	</tr>
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancelhead()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset><input type=hidden id=method value='savehead'>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff  
OPEN_BOX();



$frm[0]=$frm[1]=$frm[2]=$frm[3]=$frm[4]=$frm[5]=$frm[6]=$frm[7]=$frm[8]='';
$_SESSION['userprosedur'] = array();

$frm[0].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr> 	 
		 <td valign=top>".$_SESSION['lang']['tujuan']."</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keterangantujuan style=\"width:900px;height:100px\" rows='5' cols='100' ></textarea></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=savetujuan()>".$_SESSION['lang']['save']."</button>
		<button onclick=bataltujuan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[0].="
<div id='listtujuan'>
</div>";


$frm[1].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr> 	
		 <td valign=top>Ruang Lingkup</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keteranganruanglingkup style=\"width:900px;height:100px\" rows='10' cols='100' ></textarea></td>
	</tr>
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=saveruanglingkup()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelruanglingkup() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="
<div id='listruanglingkup'>
</div>";



$frm[2].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['nourut']."</td>
        <td>:</td>
        <td><input type=text id=nouruttanggungjawab  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:50px;\"></td>
	</tr>
	
	<tr> 	 
		 <td valign=top>".$_SESSION['lang']['keterangan']."</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keterangantanggungjawab style=\"width:900px;height:100px\" rows='5' cols='100' ></textarea></td>
	</tr>
	
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=savetanggungjawab()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceltanggungjawab() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[2].="
<div id='listtanggungjawab'>
</div>";




$frm[3].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['nourut']."</td>
        <td>:</td>
        <td><input type=text id=nourutreferensi  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:50px;\"></td>
	</tr>	
	<tr> 	 
		 <td valign=top>".$_SESSION['lang']['keterangan']."</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keteranganreferensi style=\"width:900px;height:100px\" rows='5' cols='100' ></textarea></td>
	</tr>
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=savereferensi()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelreferensi() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[3].="
<div id='listreferensi'>
</div>";


#= definisi

$frm[4].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['nourut']."</td>
        <td>:</td>
        <td><input type=text id=nourutdefinisi  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:50px;\"></td>
	</tr>	
	<tr> 	 
		 <td valign=top>".$_SESSION['lang']['keterangan']."</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keterangandefinisi style=\"width:900px;height:100px\" rows='5' cols='100' ></textarea></td>
	</tr>
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=savedefinisi()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceldefinisi() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[4].="
<div id='listdefinisi'>
</div>";


#= ketentuan umum
$frm[5].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['nourut']."</td>
        <td>:</td>
        <td><input type=text id=nourutketentuanumum  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:50px;\"></td>
	</tr>	
	<tr> 	 
		 <td valign=top>".$_SESSION['lang']['keterangan']."</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keteranganketentuanumum style=\"width:900px;height:100px\" rows='5' cols='100' ></textarea></td>
	</tr>
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=saveketentuanumum()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelketentuanumum() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[5].="
<div id='listketentuanumum'>
</div>";





$frm[6].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['nourut']."</td>
        <td>:</td>
        <td><input type=text id=nourutprosedur  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:50px;\"></td>
	</tr>	
	<tr> 	 
		 <td>Aktivitas</td>
		 <td > : </td>
		 <td><input type=text id=keteranganprosedur  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:200px;\"></td>
	</tr>
	
	<tr>
		<td style='vertical-align:top'>User</td>
		<td style='vertical-align:top'>:</td>
		<td>
			<table>
				<thead>
				<tr>
					<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id='newmaster'>
				</tbody>
				<tr>
					<td><select id=useridprosedur style='width:150px;'>".$optkaryawan."</select></td>
					<td style='text-align:center;vertical-align:top'>
						<img title='Tambah' class=resicon onclick=\"adduserprosedur()\" src='images/plus.png'/>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td>Batas Waktu</td>
        <td>:</td>
        <td><input type=text id=bataswaktuprosedur  onkeypress='return_angka_doang(event)' class=myinputtextnumber style=\"width:50px;\"></td>
	</tr>	
	
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=saveprosedur class=mybutton onclick=saveprosedur()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelprosedur() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>
<fieldset>
            <legend>".$_SESSION['lang']['list']." Upload</legend>
            <table cellspacing='1' border='0' id='uploadpopup'>
                <tr>
                    <td>Filename</td>
                    <td>:</td>
                    <td>
                        <input type='file' name='upload' id='upload' >
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"submitfile()\">Submit</button>
                    </td>
                </tr>
            </table>
            <p />
            <table class='sortable' cellspacing='1' border='0'>
                <thead>
                <tr class=rowheader>
                    <td align='center' width=50px>No.</td>
                    <td align='center' width=50px>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center' width=50px>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
$frm[6].="
<div id='listprosedur'>
</div>";




$frm[7].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
	<tr>
		<td>No. Formulir</td>
        <td>:</td>
        <td><input type=text id=nourutlampiran  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
	</tr>	
	<tr> 	 
		 <td valign=top>".$_SESSION['lang']['keterangan']."</td>
		 <td valign=top> : </td><td>
		 <textarea onkeypress=\"return tanpa_kutip(event);\" id=keteranganlampiran style=\"width:900px;height:100px\" rows='5' cols='100' ></textarea></td>
	</tr>
	<tr> 	 
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=savelampiran()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancellampiran() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[7].="
<div id='listlampiran'>
</div>";


$frm[8].="
<div id='listperubahan'>
</div>";

$hfrm[0]=strtoupper($_SESSION['lang']['tujuan']);
$hfrm[1]=strtoupper('Ruang Lingkup');
$hfrm[2]=strtoupper('TANGGUNG JAWAB');
$hfrm[3]=strtoupper('referensi');
$hfrm[4]=strtoupper('definisi');
$hfrm[5]=strtoupper('ketentuan umum');
$hfrm[6]=strtoupper('prosedur');
$hfrm[7]=strtoupper('lampiran');
$hfrm[8]=strtoupper('perubahan');



//KAS	HUTANG	BAPP	SPK	PAD	REKAP


//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,75,1100);	

CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>
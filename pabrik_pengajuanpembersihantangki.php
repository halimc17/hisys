<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/pabrik_pengajuanpembersihantangki.js?v=<?php echo time(); ?>'></script>
<!--deklarasi untuk option-->
<?php
$optunit=$optakunht=$opttipetransaksi=$optmatauang=$optunitpenerima=$optnorekpenerima=$optrekening="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(13);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 



$emodul = "CUCITANGKI";
@$arrmodul = getmodulefil($emodul);
foreach($arrmodul as $key=>$val){
	@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
}


#<!--HEADER UNTUK BUAT BARU SAMA LIST-->

// echo"<div id=action_list>";//buka div


echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_pengajuanpembersihantangki').'</span>');
echo"<table border=0>
     <tr >
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	echo"<table>";
	echo"
			<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>		
				<td>
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>		
				<td>
					<select id=kodeorgsch  style=\"width:155px;\" >'".$optunit."'</select>
				</td>
				
			<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>			
				</td>	
			</tr>	
			<tr>
			<td></td><td></td>
            <td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
        </tr>
	</table>";
echo"</fieldset></td>";

echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
// echo"<div id=listdata style=display:none>";//buka list data
echo"<div id=listdata style=display:block>";//buka list data
// OPEN_BOX();
OPEN_BOX('','');
// OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br>');
    echo "
    <div class=table-scroll style='height:65vh'>";
           echo " <table cellpading=1 cellspacing=1 border=0 class=sortable >
            <thead>
                <tr class=rowheader>
					<th  align=center>".$_SESSION['lang']['nourut']."</th>
					<th  align=center>".$_SESSION['lang']['notransaksi']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th  align=center>".$_SESSION['lang']['tglcucitangki']."</th>
                    <th  align=center>".$_SESSION['lang']['unit']."</th>
                    <th  align=center>".$_SESSION['lang']['kodetangki']."</th>
                    <th  align=center>".$_SESSION['lang']['stok']."</th>
                    <th  align=center>".$_SESSION['lang']['kodebarang']."</th>
                    <th  align=center>".$_SESSION['lang']['keterangan']."</th>
					 <th  align=center>".$_SESSION['lang']['dibuatoleh']."</th> 
                    <th  align=center>".$_SESSION['lang']['approval_status']."</th> 
                    <th  align=center colspan=5 style=width:50px>".$_SESSION['lang']['action']." </th> 
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
			<tfoot id=footData></tfoot>
             </table>
	</div>";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');

//jumlah dan kurs no 1 dan 2, agar mudah remove comma di js
$arrht="###notransaksi###kodebarang###keterangan###kodeorg###tanggal###kodetangki###sawal###tanggalcuci";

echo "<fieldset>";
// echo "<fieldset style=float:left>";
echo "<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi  size=20 disabled class=myinputtext style=\"width:150px;\"></td>
		
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><select id=kodebarang style=\"width:155px;\">".$optbarang."</select></td>

		
		<td  valign=top>".$_SESSION['lang']['keterangan']."</td>
		<td  valign=top>:</td>
		<td  valign=top rowspan=3><textarea onkeypress=\"return tanpa_kutip(event)\" id=keterangan style=\"width:150px;\" rows=3></textarea></td>

	</tr>	
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id=kodeorg onchange=gettangki() style=\"width:154px;\" >".$optunit."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']." Pengajuan</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal name=tanggal  readonly  onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px; />
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tangki']."</td>
		<td>:</td>
		<td><select id=kodetangki onchange=getbarang() style=\"width:154px;\" >".$opttangki."</select></td>
		

		<td>".$_SESSION['lang']['saldoawal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=sawal name=sawal onkeyup=z.numberFormat('sawal',2);  style=\"width:150px;\" onkeypress=\"return angka_doang(event)\">
	</tr>

	<tr>
		<td>".$_SESSION['lang']['tglcucitangki']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggalcuci name=tanggalcuci  readonly  onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px; />	
		<td></td>
		<td></td>
		<td></td>


		
		
	</tr>
	<tr>
		<td align=center colspan=2></td>
		<td>
			<button class=mybutton onclick=saveht('".$arrht."')>".$_SESSION['lang']['save']."</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo"</div>";//<input type=hidden id=method value='insertht'>	
#- <!--UNTUK BUAT FORM INPUT HEADER-->
$border='0';
echo "<div id=detail style=display:none>";
// echo "<div id=detail style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span><br>');
$frm[0]='';
$frm[0].="<fieldset style='float:left'>
		<legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='submitfile()'>Submit</button>
					<button class=mybutton onclick='loadfiles()'>Selesai</button>
				</td>
				
			</tr>
		</table>";
$frm[0].="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center'>".$_SESSION['lang']['nourut']."</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";	
$hfrm[0]=strtoupper($_SESSION['lang']['file']);
drawTab('FRM',$hfrm,$frm,100,'auto');   
CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>
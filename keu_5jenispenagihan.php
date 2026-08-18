<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/keu_5jenispenagihan.js?v=<?php echo time(); ?>></script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php

#= option
$optdata=$optjurnal=$optakun=$optkodebarang=$optpph22pdf=$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";	

#= option	
$str = "SELECT * from ".$dbname.".keu_5akun where length(noakun)=7";		
$res=fetchdata($str);
foreach($res as $bar){
	$optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
}	

$str = "SELECT * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";		
$res=fetchdata($str);
foreach($res as $bar){
	$optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
}

$optjurnal.="<option value='0'>".$_SESSION['lang']['tidak']."</option>";	
$optjurnal.="<option value='1'>".$_SESSION['lang']['ya']."</option>";

$optpph22pdf.="<option value='0'>".$_SESSION['lang']['tidak']."</option>";	
$optpph22pdf.="<option value='1'>".$_SESSION['lang']['ya']."</option>";	

$optstatus.="<option value='1'>".$_SESSION['lang']['aktif']."</option>";	
$optstatus.="<option value='0'>".$_SESSION['lang']['tidakaktif']."</option>";	

#= inisialisasi fild input
$arrht="###kodejenis###namajenis###initial###printout###jurnal###jurnalppn###status";
$arrdt="###kodejenis###kodebarang###noakunpiutang###noakunsales###noakunuangmuka###noakunppn###pph22";


#= HEADER untuk BUAT BARU, LIST DATA dan CARI
/*
<td>".$_SESSION['lang']['tanggal']."</td>
	<td>:</td>		
	<td>
		<input type=text class=myinputtext id=tanggalsch name=tanggalsch readonly onmousemove=setCalendar(this.id) onkeypress=return false; onchange=loaddataht();  maxlength=10 style=width:61px;/>		
	</td>

*/


OPEN_BOX('','<span class=judul>'.getMenu('keu_5jenispenagihan').'</span>');
echo "<div>";
echo "	<table cellspacing=1 border=0>
			<tbody>
				<tr valign=middle>
					<td style=width:100px;cursor:pointer; onclick=buatbaru() align=center>
						<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>
						".$_SESSION['lang']['new']."
					</td>
					<td style=width:100px;cursor:pointer; onclick=displaylist() align=center>
						<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>
						".$_SESSION['lang']['list']."
						<td>
					</td>
					<td>
						<fieldset style='width:auto;'>
							<legend>".$_SESSION['lang']['find']."</legend>
							<table>
								<tr>
									<td align=left>".$_SESSION['lang']['kode']."</td>
									<td>:</td>
									<td><input  type=text id=kodejeniscari class=myinputtext style=width:150px;></td>
									
									<td align=left>".$_SESSION['lang']['nama']."</td>
									<td>:</td>
									<td><input  type=text id=namajeniscari class=myinputtext style=width:150px;></td>
								</tr>
								<tr>
								<td></td>
								<td></td>
								<td>
									<button class=mybutton onclick=loaddataht()>".$_SESSION['lang']['find']."</button>
									<button class=mybutton onclick=displaylist()>".$_SESSION['lang']['cancel']."</button>
								</td>
								</tr>
							</table>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
	</div>";
CLOSE_BOX();


#=  LIST DATA HEADER

echo "<div id=listdata>";
OPEN_BOX();
echo " 	<fieldset style='float:left; width:auto;'>
			<legend>" . $_SESSION['lang']['list'] . "</legend>
			<div>
				<table class=sortable cellspacing=1 border=0>
					<thead>
						<tr class=rowheader>
							<td align=center>".$_SESSION['lang']['nourut']."</td>
							<td align=center>".$_SESSION['lang']['kode']."</td>
							<td align=center>".$_SESSION['lang']['nama']."</td>
							<td align=center>".$_SESSION['lang']['inisial']."</td>
							<td align=center>".$_SESSION['lang']['print']."</td>
							<td align=center>".$_SESSION['lang']['jurnal']." ".$_SESSION['lang']['piutang']."</td>
							<td align=center>".$_SESSION['lang']['jurnal']." ".$_SESSION['lang']['ppn']."</td>
							<td align=center>".$_SESSION['lang']['status']."</td>
							<td align=center>".$_SESSION['lang']['action']."</td>
						</tr>
					</thead>
					<tbody id=container>
						<script>loaddataht(0)</script>
					</tbody>
					<tfoot id=footData>
					</tfoot>
				</table>
			</div>
		</fieldset>";
CLOSE_BOX();
echo"</div>";


#= form inputan header
echo "<div id=header style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');
echo 	"<fieldset style='float:left; widht:auto;'>
			<legend>".$_SESSION['lang']['entryForm']."</b></legend> 
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td align=left>".$_SESSION['lang']['kode']."</td>
					<td>:</td>
					<td><input  type=text id=kodejenis class=myinputtext style=width:150px;></td>
					
					<td align=left>".$_SESSION['lang']['inisial']."</td>
					<td>:</td>
					<td><input  type=text id=initial class=myinputtext  placeholder='Untuk Initial nomor dokumen' style=width:150px;></td>
					
					<td align=left>".$_SESSION['lang']['jurnal']." Piutang</td>
					<td>:</td>
					<td><select id=jurnal style=width:155px>".$optjurnal."</select></td>

					<td align=left>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td><select id=status style=width:155px>".$optstatus."</select></td>
				</tr>
				<tr>
					<td align=left>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><input  type=text id=namajenis class=myinputtext style=width:150px;></td>
					
					<td align=left>".$_SESSION['lang']['print']."</td>
					<td>:</td>
					<td><input  type=text id=printout placeholder='Untuk Judul printout dokumen' class=myinputtext style=width:150px;></td>		
					
					<td align=left>".$_SESSION['lang']['jurnal']." ".$_SESSION['lang']['ppn']."</td>
					<td>:</td>
					<td><select id=jurnalppn style=width:155px>".$optjurnal."</select></td>
				</tr>
		
					
				<tr>
					<td></td>
					<td></td>
					<td>
						<input type=hidden id=methodht value='saveht'>
						 <button class=mybutton onclick=saveht('".$arrht."')>".$_SESSION['lang']['save']."</button>
						 <button class=mybutton onclick=hapusht()>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
CLOSE_BOX();
echo"</div>";
#= form detail
echo "<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span><br>');
// echo"<fieldset style='float:left; widht:auto;'>
echo"<fieldset style='widht:auto;'>
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><select id=kodebarang style=width:155px>".$optkodebarang."</select></td>
		
		<td  align=left>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['piutang']."</td> 
		<td>:</td>
		<td><select id=noakunpiutang style=width:155px>".$optakun."</select></td>
		
		<td align=left>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['mutu']."</td>
		<td>:</td>
		<td><select id=noakunklaimmutu style=width:155px>".$optakun."</select></td>
		
		<td  align=left>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['ppn']."</td> 
		<td>:</td>
		<td><select id=noakunppn style=width:155px>".$optakun."</select></td>
		
	</tr>
	<tr>
		<td  align=left>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['uangmuka']."</td> 
		<td>:</td>
		<td><select id=noakunuangmuka style=width:155px>".$optakun."</select></td>
	
		<td  align=left>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['penjualan']."</td> 
		<td>:</td>
		<td><select id=noakunsales style=width:155px>".$optakun."</select></td>
		
		<td align=left>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['susut']."</td>
		<td>:</td>
		<td><select id=noakunklaimsusut style=width:155px>".$optakun."</select></td>
		
		
		<td align=left>PDF ".$_SESSION['lang']['pph']." 22</td>
		<td>:</td>
		<td><select id=pph22 style=width:155px>".$optpph22pdf."</select></td>
	</tr>
	
	<tr><td colspan=2></td><td>
	<input type=hidden id=methoddt value='savedt'>
		 <button class=mybutton onclick=savedt('".$arrdt."')>".$_SESSION['lang']['save']."</button>
		 <button class=mybutton onclick=hapusdt()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>	
</table>
</fieldset>";

/*
kodebarang	noakunpiutang	noakunsales	noakunuangmuka	noakunppn
*/

echo"<br>";
// echo"<fieldset style='float:left; widht:auto;'>
echo"<fieldset style='widht:auto;'>
            <legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead wid>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td> 
                    <td  align=center>".$_SESSION['lang']['kodebarang']."</td> 
                    <td  align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['uangmuka']."</td> 
                    <td  align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['piutang']."</td> 
                    <td  align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['penjualan']."</td> 
                     
                    <td  align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['mutu']."</td> 
                    <td  align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['susut']."</td> 
					<td  align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['ppn']."</td>
                    <td  align=center>".$_SESSION['lang']['action']." </td> 
                </tr>
			
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";
CLOSE_BOX();

echo close_body();
?>
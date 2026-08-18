<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/log_retursuppliernoninventory.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<!--deklarasi untuk option-->
<?php
$optunit=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
	if($val==$_SESSION['empl']['lokasitugas']){
		$optunit.="<option value='".$val."' selected>".$val." - ".$nama."</option>";
	}else{
		$optunit.="<option value='".$val."' >".$val." - ".$nama."</option>";
	}
} 


$arrtipe=array('CO'=>'CO','SO'=>'SO','NO'=>'NO');
foreach($arrtipe as $key=>$data){
	$opttipe.="<option value='".$key."'>".$data."</option>";
}

#= untuk coa ht
$arrtipeunit=array();
$arrtipeunit=getOrgDetail(15);
$str="select * from ".$dbname.".keu_5akun where 
	kasbank=1 and detail=1 and aktif=1 and (pemilik='GLOBAL' or pemilik in ('".implode("','",$arrtipeunit)."'))";
$res=fetchdata($str);
foreach($res as $bar){
	@$optakunht.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
}

#= tipe transaksi masuk/keluar
$arrtransaksi=array("M"=>"Masuk","K"=>"Keluar"); 
foreach($arrtransaksi as $val=>$nama){
    @$opttipetransaksi.="<option value='".$val."'>".$nama."</option>";
}  

$arrmodul = getmodulefil($emodul);
foreach($arrmodul as $key=>$val){
    $optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
}

#<!--HEADER UNTUK BUAT BARU SAMA LIST-->

// echo"<div id=action_list>";//buka div


echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('log_retursuppliernoninventory').'</span>');
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
					<select id=kodeorgsch  style=\"width:154px;\"  onchange=getrekeningsch()>'".$optunit."'</select>
				</td>
			</tr>	
			
			<tr>
			<td></td><td></td>
            <td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
						<button class=mybutton onclick=loaddatapdf()>".$_SESSION['lang']['pdf']."</button></td>
        </tr>
	</table>";
echo"</fieldset></td>";
				// <td><input class=myinputtextnumber id=jumlahsch onkeyup=z.numberFormat('jumlahsch',2); style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>

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
    // echo " <div class=table-scroll style='height:700px'>";
           echo " <table cellpadding=2 cellspacing=1 border=0 class=sortable  width=100%>
            <thead>
                <tr style='text-align:center;font-weight:bold' class='rowheader'>
					<th>".$_SESSION['lang']['nourut']."</th>
					<th>".$_SESSION['lang']['notransaksi']."</th>
					<th>".$_SESSION['lang']['tipe']."</th>
					<th>".$_SESSION['lang']['perusahaan']."</th>
					<th>".$_SESSION['lang']['unit']."</th>
					<th>".$_SESSION['lang']['tanggal']."</th>
					<th>".$_SESSION['lang']['nopo']."</th>
					<th>".$_SESSION['lang']['namasupplier']."</th>
					<th>".$_SESSION['lang']['termin']."</th>
					<th>".$_SESSION['lang']['dibuat']."</th>
					<th hidden>".$_SESSION['lang']['approval_status']."</th> 
					<th hidden>".$_SESSION['lang']['posting']."</th>
					<th align='center' colspan='4'>Action</th>
				</tr>
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
			<tfoot id=footData></tfoot>
             </table>";
	// echo"</div>";
CLOSE_BOX();
echo "</div>";//tutup list data

#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');

$arrht="###notransaksi###nopo###unit###tipe###notransaksireferensi###supplierid###tanggal###namasupplier###keterangan###termin";

echo "<fieldset>";
// echo "<fieldset style=float:left>";
echo "<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td style=\"width:175px;\">".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\"></td>
		
		<td style=\"width:175px;\">".$_SESSION['lang']['nopo']."</td>
		<td>:</td>		
		<td><input type=text id=nopo size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=unit  style=\"width:154px;\" >'".$optunit."'</select>
		</td>
		
		<td style=\"width:175px;\">".$_SESSION['lang']['tipe']."</td>
		<td>:</td>		
		<td><input type=text id=tipe size=20 disabled  class=myinputtext style=\"width:150px;\">
		</td>
	
	</tr>

	
	<tr>
		<td style=\"width:175px;\">".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['penerimaan']."</td>
		<td>:</td>		
		<td><input type=text id=notransaksireferensi size=20 raeadonly class=myinputtext style=\"width:150px;\">
			<button class=mybutton id=buttonap onclick=getdata('notransaksi')>".$_SESSION['lang']['find']."</button>
		</td>
	
		<td style=\"width:175px;\">".$_SESSION['lang']['kodesupplier']."</td>
		<td>:</td>		
		<td><input type=text id=supplierid size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>


	
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal name=tanggal  style=\"width:150px;\" value=".date('d-m-Y')." readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	
		</td>
		
		<td style=\"width:175px;\">".$_SESSION['lang']['namasupplier']."</td>
		<td>:</td>		
		<td><input type=text id=namasupplier size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>

	<tr>
	
		<td>".$_SESSION['lang']['keterangan']."</td>
		<td>:</td>		
		<td><input type=text id=keterangan class=myinputtext style=\"width:150px;\"></td>
		
		<td style=\"width:175px;\">".$_SESSION['lang']['termin']."</td>
		<td>:</td>		
		<td><input type=text id=termin size=20 disabled class=myinputtext style=\"width:150px;\"></td>
		
	</tr>
	
	<tr>
		<td align=center colspan=2></td>
		<td colspan=6>
		
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
$frm[1]='';



	$frm[0].="
    <fieldset>
            <legend><b>".$_SESSION['lang']['detail']."</b></legend>
            <table cellpading=2 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td align=center>".$_SESSION['lang']['nopp']." </td> 
                    <td align=center>".$_SESSION['lang']['kodebarang']." </td> 
                    <td align=center>".$_SESSION['lang']['satuan']." </td> 
                    <td align=center>".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['transaksi']."</td> 
                    <td align=center>".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['retur']."</td> 
                    <td align=center>".$_SESSION['lang']['hargasatuan']." </td> 
					<td align=center>".$_SESSION['lang']['subunit']."</td>
					<td align=center>".$_SESSION['lang']['subunit']." ".$_SESSION['lang']['detail']."</td>
					<td align=center>".$_SESSION['lang']['kodekegiatan']." </td> 
					<td align=center>".$_SESSION['lang']['nopo']."</td> 
					<td align=center>".$_SESSION['lang']['catatan']."</td>
					<td align=center colspan='2'>".$_SESSION['lang']['action']." </td> 
                </tr>  
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";

$frm[1].="<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria ."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload'>
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='statusbar'></p>
					<p id='loaded_n_total'></p>
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
//$frm[1].="<fieldset>
//		<legend>".$_SESSION['lang']['list']."</legend>";
$frm[1].="<br><table class='sortable' cellspacing='1' border='0' cellpadding=5 width=50%>
			<thead>
			<tr class=rowheader>
				<th align='center'>".$_SESSION['lang']['nourut']."</th>
				<th align='center'>File Type</th>
				<th align='center' hidden>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center' colspan=2>Action</th>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";	
	
	
$hfrm[0]=strtoupper($_SESSION['lang']['transaksi']);
$hfrm[1]=strtoupper($_SESSION['lang']['file']);
drawTab('FRM',$hfrm,$frm,100,'auto');   
CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>
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
<script language=javascript src='js/keu_adjustmentlaporankeuangan.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<!--deklarasi untuk option-->
<?php
$optunit=$optjenis=$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#= untuk unit ht
// $arrunit=array();
// $arrunit=getOrgDetail(1);
// foreach($arrunit as $val=>$nama){
	// // if($val==$_SESSION['empl']['lokasitugas']){
		// // $optunit.="<option value='".$val."' selected>".$val." - ".$nama."</option>";
	// // }else{
		// $optunit.="<option value='".$val."' >".$val." - ".$nama."</option>";
	// // }
// } 
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	if(strlen($key)==4){		
		$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}
		$optunit.="<option value=".$key.">".$key." - ".$val."</option>";
		$n=$d;
		if($d!=$n){			
			$optunit.="</optgroup>";
		}
	}
}


$str="select * from ".$dbname.".keu_5mesinlaporanht where aktif=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optjenis.="<option value='".$bar['namalaporan']."'>".$bar['ket1']."</option>";
}

$optjenis.="<option value='HPPCPO'>HPPCPO</option>";
$optjenis.="<option value='HPPPK'>HPPPK</option>";

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 36";
$res=fetchdata($str);
foreach($res as $bar){
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

#<!--HEADER UNTUK BUAT BARU SAMA LIST-->

// echo"<div id=action_list>";//buka div


echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('keu_adjustmentlaporankeuangan').'</span>');
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
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>		
			<td>
				<select id=kodeunitsch  style=\"width:154px;\">'".$optunit."'</select>
			</td>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>		
			<td>
				<select id=periodesch  style=\"width:154px;\" >'".$optperiode."'</select>
			</td>
		</tr>	
		<tr>	
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>		
			<td><select id=jenissch  style=\"width:154px;\">'".$optjenis."'</select></td>
			<td>".$_SESSION['lang']['jumlah']."</td>
			<td>:</td>		
			<td><input class=myinputtextnumber id=jumlahsch  onkeyup=z.numberFormat('jumlahsch',2); style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>
		</tr>	
		<tr>
			<td>".$_SESSION['lang']['kode']."</td>
			<td>:</td>		
			<td>
				<input type=text id=codesch size=50 class=myinputtext style=\"width:150px;\">
			</td>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>		
			<td>
				<input type=text id=keterangansch size=50 class=myinputtext style=\"width:150px;\">
			</td>
		</tr>
		<tr>
			<td></td><td></td>
            <td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
						
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
    echo " <div class=table-scroll style='height:65vh'>";
           echo " <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>".$_SESSION['lang']['unit']."</th>
					<th align=center>".$_SESSION['lang']['jenis']."</th>
					<th align=center>".$_SESSION['lang']['kode']."</th>
					<th align=center>".$_SESSION['lang']['periode']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['keterangan']."</th>
					<th align=center>".$_SESSION['lang']['createby']."</th> 
					<th align=center>".$_SESSION['lang']['createtime']."</th> 
					<th align=center>".$_SESSION['lang']['updateby']."</th> 
					<th align=center>".$_SESSION['lang']['updatetime']."</th> 
					<th align=center colspan=2>".$_SESSION['lang']['action']."</th> 
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
			<tfoot id=footData></tfoot>
             </table>";
	echo"</div>";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');

//jumlah dan kurs no 1 dan 2, agar mudah remove comma di js
$arrht="###notransaksi###kodeunit###periode###jenis###code###jumlah###keterangan";


echo "<fieldset>";
// echo "<fieldset style=float:left>";
echo "<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr hidden>	
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td><input class=myinputtextnumber id=notransaksi></td>
	</tr>		
	<tr>		
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select id=kodeunit  style=\"width:154px;\">'".$optunit."'</select>
		</td>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>		
		<td>
			<select id=periode  style=\"width:154px;\" >'".$optperiode."'</select>
		</td>
	</tr>	
	<tr>	
		<td>".$_SESSION['lang']['jenis']."</td>
		<td>:</td>		
		<td><select id=jenis  style=\"width:154px;\">'".$optjenis."'</select></td>
		<td>".$_SESSION['lang']['jumlah']."</td>
		<td>:</td>		
		<td><input class=myinputtextnumber id=jumlah   onkeyup=z.numberFormat('jumlah',2); style=\"width:150px;\" onkeypress='return_tanpa_kutip_dan_sepasi(event)' /></td>
		
	</tr>	
	<tr>
	
		<td>".$_SESSION['lang']['kode']."</td>
		<td>:</td>		
		<td>
			<input type=text id=code size=50 class=myinputtext style=\"width:150px;\">
		</td>
		
		<td>".$_SESSION['lang']['keterangan']."</td>
		<td>:</td>		
		<td>
			<input type=text id=keterangan size=50 class=myinputtext style=\"width:150px;\">
		</td>
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
echo close_body();		////<input type=hidden id=method value='insert'>	
?>
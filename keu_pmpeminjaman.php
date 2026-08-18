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
<script language=javascript1.2 src='js/keu_pmpeminjaman.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script language=javascript1.2 src=js/keu_pmsukubunga.js></script>
<!--deklarasi untuk option-->
<?php

OPEN_BOX('','<span class=judul>'.getMenu('keu_pmpeminjaman').'</span>');

$optnoloan=$optper=$optunit=$optjatuhtempo=$optjenis=$optnoakun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
    $sBank="select * from ".$dbname.".keu_5akunbank where pemilik='".$kei."'";
    $rBank=fetchData($sBank);
    if(count($rBank)!=0){
        $optunit.="<option value='".$kei."'>".$fal."</option>";
    }
}
for($x=0;$x<=12;$x++){
	$dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
	$optper.="<option value=".date("Y-m",$dte).">".date("m-Y",$dte)."</option>";
}
/*
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
*/


for($i=1;$i<=28;){
	if(strlen($i)<2){
		$i="0".$i;
	}
   $optjatuhtempo.="<option value=".$i.">".$i."</option>";
   $i++;
}

$optjenis.="<option value='KRK'>KRK</option>";
$optjenis.="<option value='KISI'>KISI</option>";

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
	 <td><fieldset style=width:500px><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table width=100%>";
	echo"
		<tr>
			<td>".$_SESSION['lang']['nopeminjaman']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber id=notransaksisch style=width:100px onkeypress='return_tanpa_kutip(event)' ></td>
		
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id=ptsch style=\"width:100px;\">'".$optunit."'</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>
			<td><select id=jenissch style=\"width:100px;\">'".$optjenis."'</select></td>
		
			<td>".$_SESSION['lang']['noakun']."</td>
			<td>:</td>
			<td><select id=noakunsch style=\"width:100px;\">'".$optnoakun."'</select></td>
		</tr>
			<td colspan=2></td>
            <td><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
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
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['nopeminjaman']."</td>
					<td  align=center>".$_SESSION['lang']['unit']."</td>
                    <td  align=center>".$_SESSION['lang']['jenis']."</td>
                    <td  align=center>Tipe Perhitungan Pokok</td>
                    <td  align=center>".$_SESSION['lang']['bank']."</td>
                    <td  align=center>".$_SESSION['lang']['norekeningbank']."</td>
                    <td  align=center>".$_SESSION['lang']['jumlahfasilitas']."</td>
                    <td  align=center>".$_SESSION['lang']['jangkawaktu']."</td>
                    <td  colspan='3' align=center>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
               
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
                 <script>loaddata(0)</script>
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php
$arrPokok=array("0"=>"Per Pencairan","1"=>"Total Pencairan");
foreach ($arrPokok as $key => $val) {
	$optTipePokok.="<option value='".$key."'>".$val."</option>";
}
echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0 >
   
	<tr>	
		<td>".$_SESSION['lang']['nopeminjaman']."</td>
		<td> : </td>
		<td><input type=text class=myinputtext id=notransaksi disabled style=width:200px onkeypress='return tanpa_kutip(event)' ></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Commitment Periode</td>
		<td> : </td>
		<td><input type='text' class='myinputtext' id='komitmenperiode' style='width:300px;' maxlength='200' onkeypress='return tanpa_kutip(event)' ></td>
	</tr>
	<tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit onchange=\"getNamabank(this.value)\" style=\"width:205px;\">'".$optunit."'</select></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Availability Periode</td>
		<td> : </td>
		<td><input type='text' class='myinputtext' id='availabilityperiode' style='width:300px;' maxlength='200' onkeypress='return tanpa_kutip(event)' ></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['namabank']."</td>
        <td>:</td>
        <td><select id=namabank onchange=\"getNoakun(this.value)\" style=\"width:205px;\"></select></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Grace Periode</td>
		<td> : </td>
		<td><input type='text' class='myinputtext' id='graceperiode' style='width:300px;' maxlength='200' onkeypress='return tanpa_kutip(event)' ></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['norek']."</td>
        <td>:</td>
        <td><select id=noakun style=\"width:205px;\"></select></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Biaya Kredit</td>
		<td> : </td>
		<td><input type='text' class='myinputtext' id='biayakredit' style='width:300px;' maxlength='200' onkeypress='return tanpa_kutip(event)' ></td>
    
    </tr>
	<tr>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id=jenis style=\"width:205px;\">'".$optjenis."'</select></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Suku Bunga</td>
		<td> : </td>
		<td><input type='text' class='myinputtext' id='sukubunga' style='width:300px;' maxlength='200' onkeypress='return tanpa_kutip(event)' ></td>
    
    </tr>
	<tr>
		<td>".$_SESSION['lang']['jenis']." Fasilitas</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=jenisfasilitas style=width:200px ></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Pinalti</td>
		<td> : </td>
		<td><input type='text' class='myinputtext' id='pinalti' style='width:300px;' maxlength='200' onkeypress='return tanpa_kutip(event)' ></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tujuan']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tujuan style=width:200px ></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Keterangan</td>
		<td> : </td>
		<td rowspan='2'><textarea id='keterangan' maxlength='500' style='width:280px;height:20px;'></textarea></td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['jumlahfasilitas']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=jumlahfasilitas value=0  style=width:200px onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('jumlahfasilitas');\"></td>
		<td></td>
		<td></td>
		<td></td>
		
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['jangkawaktu']."</td>
		<td> : </td>
		<td>
			<input type=text class=myinputtext id=jangkawaktu onmousemove=setCalendar(this.id) style=width:200px  onkeypress=return false;  size=10 maxlength=10 />
		</td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>".$_SESSION['lang']['jumlahbulan']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=jumlahbulan value=0  style=width:50px onkeypress='return angka_doang(event)'>
			Jenis Pinjaman : 
			<input type='text' class='myinputtext' id='jenispinjaman' style='width:150px;' maxlength='200' onkeypress='return tanpa_kutip(event)' >
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jatuhtempo']."</td>
		<td> : </td>
		<td><select id=jatuhtempo style=\"width:205px;\">'".$optjatuhtempo."'</select></td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>Tipe Perhitungan Pokok</td>
		<td>:</td>
		<td><select id=tpPokok style=\"width:205px;\">'".$optTipePokok."'</select></td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=newdata()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";
// echo "<div id=detail>";//buka diff
OPEN_BOX();
$frm[0]='';
$frm[1]='';
// style='float:left;'
$frm[0].="<fieldset><legend>".$_SESSION['lang']['form']." Pencairan</legend>
<table>
	<tr>	
		<td>".$_SESSION['lang']['nopencairan']."</td>
		<td> : </td>
		<td><input type=text class=myinputtext id=noloanpencairan style=width:100px onkeypress='return_tanpa_kutip(event)' ></td>
	</tr>   
    <tr>
		<td>".$_SESSION['lang']['tanggalpencairan']."</td>
		<td> : </td>
		<td>
			<input type=text class=myinputtext id=tanggalpencairan onmousemove=setCalendar(this.id) style=width:100px  onkeypress=return false;  size=10 maxlength=10 readonly/>
		</td>
	</tr>
    <tr>	
		<td>".$_SESSION['lang']['jumlahpencairan']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=jumlahpencairan value=0  style=width:100px onkeyup=\"z.numberFormat('jumlahpencairan');\" onkeypress='return angka_doang(event)' ></td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['jatuhtempo']."</td>
		<td> : </td>
		<td><select id=jatuhtempoCair style=width:100px>".$optjatuhtempo."</select></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td colspan=100>
		<button class=mybutton onclick=savepencairan()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelpencairan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$frm[0].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
  <div id=listpencairan></div>
  </fieldset>";


$frm[1].="<fieldset><legend>".$_SESSION['lang']['form']." Angsuran</legend>
<table>
	<tr>	
		<td>".$_SESSION['lang']['tanggalpembayaran']."</td>
		<td> : </td>
		<td>
			<input type=text class=myinputtext id=tanggalpembayaranangsuran onchange=sukubunga(); onmousemove=setCalendar(this.id) style=width:100px  onkeypress=return false;  size=10 maxlength=10 readonly/>
		</td>
	</tr> 
	<tr>	
		<td>".$_SESSION['lang']['sukubunga']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=sukubungaangsuran disabled value=0 style=width:100px onkeypress='return_tanpa_kutip(event)' ><span>%</span>
		<button class=mybutton onclick='loadsukubunga();'>Tambah Suku Bunga</button></td>
	</tr> 
	<tr>
        <td>".$_SESSION['lang']['nopencairan']."</td>
        <td>:</td>
        <td><select id=noloanangsuran onchange=getByrKe() style=\"width:100px;\">".$optnoloan."</select></td>
    </tr>  
 
	<tr>
        <td>Bulan - ke</td>
        <td>:</td>
        <td><select id=bulankeangsuran style=\"width:100px;\">".$optnoloan."</select></td>
    </tr> 
	<tr>	
		<td>Periode Calculate</td>
		<td> : </td>
		<td>
			<input type=text class=myinputtext id=periodecalculate onmousemove=setCalendar(this.id,'%Y-%m') style=width:100px  onkeypress=return false;  size=10 maxlength=10 disabled/>
		</td>
	</tr> 
    <tr>	
		<td>".$_SESSION['lang']['pokok']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=pokokangsuran value=0 onkeyup=\"z.numberFormat('pokokangsuran'); return totalangsuran();\" onchange='getBungaIsi()' style=width:100px onkeypress='return angka_doang(event)' readonly ></td>
	</tr>
	 <tr id='harihutang' hidden>	
		<td>".$_SESSION['lang']['harihutang']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber value=0 id=harihutangangsuran style=width:100px onkeypress='return_tanpa_kutip(event)' readonly ></td>
	</tr>   
	<tr>	
		<td>".$_SESSION['lang']['jumlahbunga']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=bungaangsuran onkeyup=\"z.numberFormat('bungaangsuran'); return totalangsuran();\" value=0 style=width:100px onkeypress='return angka_doang(event)' readonly  /></td>
	</tr>  

	<tr hidden>	
		<td>".$_SESSION['lang']['totalbunga']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=totalbungaangsuran value=0 style=width:100px onkeypress='return angka_doang(event)' ></td>
	</tr>  

	<tr>	
		<td>".$_SESSION['lang']['totalpembayaran']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=totalpembayaranangsuran disabled style=width:100px  onkeypress='return angka_doang(event)' readonly ></td>
	</tr>  
	 
	
	<tr>   
	
		<td colspan=2></td>
		<td colspan=100>
		<button  class=mybutton onclick=saveangsuran()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelangsuran() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
  <div id=listangsuran></div>
  </fieldset>";

$hfrm[0]=strtoupper('Pencairan');
$hfrm[1]=strtoupper('Angsuran');


//KAS	HUTANG	BAPP	SPK	PAD	REKAP


//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1100);	

CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>
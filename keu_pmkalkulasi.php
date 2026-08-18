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
<script language=javascript1.2 src='js/keu_pmkalkulasi.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php

OPEN_BOX('','<span class=judul>'.getMenu('keu_pmkalkulasi').'</span>');
$optunit=$optpt=$optjatuhtempo=$optjenis=$optnoakun=$optnotransaksi=$optper="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
    $sBank="select * from ".$dbname.".keu_5akunbank where pemilik='".$kei."'";
    $rBank=fetchData($sBank);
    if(count($rBank)!=0){
        $optunit.="<option value='".$kei."'>".$fal."</option>";
    }
}
$str = "select a.noakun,a.rekening,b.namabank from ".$dbname.".keu_5akunbank a 
left join keu_5daftarbank b on a.namabank = b.kodebank";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optnoakun.="<option value='".$bar['noakun']."'>".$bar['rekening']." &nbsp;&nbsp;&nbsp; ".$bar['namabank']."</option>";
}

$str = "select notransaksi,noakun from ".$dbname.".keu_pmpeminjamanht";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$sBankDt="select b.namabank as namabank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b 
	          on a.namabank=b.kodebank where a.noakun='".$bar['noakun']."'";
	$rBankDt=fetchData($sBankDt);
    $optnotransaksi.="<option value='".$bar['notransaksi']."'>".$rBankDt[0]['namabank']."-".$bar['notransaksi']."</option>";
}

for($x=0;$x<=12;$x++){
	$dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
	$optper.="<option value=".date("Y-m",$dte).">".date("m-Y",$dte)."</option>";
}


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
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	echo"
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><input type=text class=myinputtext placeholder=Seluruhnya id=notransaksisch style=width:100px onkeypress='return_tanpa_kutip(event)' ></td>
		
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id=ptsch style=\"width:100px;\">'".$optunit."'</select></td>
			
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td><select id=periodesch style=\"width:100px;\">'".$optper."'</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>
			<td><select id=jenissch style=\"width:100px;\">'".$optjenis."'</select></td>
		
			<td>".$_SESSION['lang']['noakun']."</td>
			<td>:</td>
			<td><select id=noakunsch style=\"width:100px;\">'".$optnoakun."'</select></td>
		</tr>
		<tr>
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
                    <td  align=center>".$_SESSION['lang']['notransaksi']."</td>
                    <td  align=center>".$_SESSION['lang']['periode']."</td>
					<td  align=center>".$_SESSION['lang']['pt']."</td>
                    <td  align=center>jenis</td>
                    <td  align=center>noakun</td>
                    <td  align=center>jumlahfasilitas</td>
                    <td  align=center>jangkawaktu</td>
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

echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 		<input type=text class=myinputtext id=notransaksi disabled style=width:100px onkeypress='return_tanpa_kutip(event)' >
echo "
<table cellspacing=1 border=0>
	<tr>	
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td> : </td>
		<td>
			<select id=notransaksi style=\"width:100px;\" onchange=getdata(this.value)>'".$optnotransaksi."'</select>
		<img id='mandor' onclick=z.elSearch('notransaksi',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
		</td>
	</tr>
</table>
<fieldset>
<legend>Header</legend>

<div id='resulthead'></div>
</fieldset>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";
// echo "<div id=detail>";//buka diff
OPEN_BOX();

echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
  <div id=detaildata></div>
  </fieldset>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>
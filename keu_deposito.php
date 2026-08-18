<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_deposito')."</span>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script type="text/javascript">
notifnoinvoiceafiliasi="<?php echo $_SESSION['lang']['notifnoinvoiceafiliasi']; ?>";
notifkontrak="<?php echo $_SESSION['lang']['notifkontrak']; ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/keu_deposito.js" /></script>

<?php

#nama PT
$optstatus=$optbank=$optunit=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $sakundbt=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)=4 and tipe='HOLDING' order by namaorganisasi asc");
// $sakundbt->setFetchMode(PDO::FETCH_ASSOC);
// while($rakun=  $sakundbt->fetch()){
//     $optunit.="<option value='".$rakun['kodeorganisasi']."'>".$rakun['namaorganisasi']."</option>";
// }

#nama unit
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
    $sBank="select * from ".$dbname.".keu_5akunbank where pemilik='".$kei."'";
    $rBank=fetchData($sBank);
    if(count($rBank)!=0){
        $optunit.="<option value='".$kei."'>".$fal."</option>";    
    }
}

#Tipe Transaksi
$arrtipe=getEnum($dbname,'keu_depositoht','jnsdeposito');
foreach($arrtipe as $kei=>$fal)
{
    switch ($kei) {
        case '1':$capt=$_SESSION['lang']['depositoberjangka'].'(Automatic Roll-Over)';break;
        case '2':$capt=$_SESSION['lang']['depositoberjangka'].'(Non Roll-Over)';break;
    }

    $opttipe.="<option value='".$kei."'>".$capt."</option>";
}

$arrstatus=array('0' => 'Non Roll-Over','1' => 'Roll-Over');
foreach ($arrstatus as $key => $value) {
    $optstatus.="<option value='".$key."'>".$value."</option>";
}


echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo $_SESSION['lang']['tipetransaksi']." : <select id=tipecr style=width:150px;>".$opttipe."</select>";
echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable >";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['unit']."</td>";
echo"<td>".$_SESSION['lang']['tipetransaksi']."</td>";
echo"<td>".$_SESSION['lang']['namabank']."</td>";
echo"<td>".$_SESSION['lang']['nourut']." Bilyet</td>";
echo"<td>".$_SESSION['lang']['nourut']." Deposito</td>";
echo"<td>".$_SESSION['lang']['tanggalvaluta']." </td>";
echo"<td>".$_SESSION['lang']['tanggaljatuhtempo']."</td>";
echo"<td>".$_SESSION['lang']['sukubunga']."</td>";
echo"<td>".$_SESSION['lang']['jumlahdeposito']." </td>";
echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td colspan=4>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";


echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table border=0 >";
echo"</tr>	
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id=pt style=width:150px; onchange='getakun()'>".$optunit."</select></td>
	 </tr>"; 
echo"</tr>  
        <td>".$_SESSION['lang']['namabank']."</td>
        <td>:</td>
        <td><select id=noakun style=width:150px;>".$optbank."</select></td>
        <td>".$_SESSION['lang']['jenisdeposito']."</td>
        <td>:</td>
        <td><select id=tipetransaksi style=width:150px; onchange='getstatus()'>".$opttipe."</select></td>
     </tr>";
echo"<tr>
		<td>".$_SESSION['lang']['nourut']." Bilyet</td>
        <td>:</td>
		<td><input type=text id=nobilyet class=myinputtext style=width:148px;></td>
        <td>".$_SESSION['lang']['nourut']." Deposito</td>
        <td>:</td>
        <td><input type=text id=nodeposito class=myinputtext style=width:148px; ></td>
	 </tr>";
echo"<tr>
        <td>".$_SESSION['lang']['tanggalvaluta']."</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tglvaluta onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:148px; maxlength=10 onchange=getBulan() /></td>
        <td>".$_SESSION['lang']['tanggal']." Jatuh Tempo</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tgltempo onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:148px; maxlength=10 onchange=getBulan() /></td>
     </tr>";
echo"<tr>
        <td>".$_SESSION['lang']['jangkawaktu']."</td>
        <td>:</td>
        <td><input type=text id=jangkawaktu class=myinputtextnumber style=width:90px; onkeypress=\"return angka_doang(event);\" disabled=disabled> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bulan</td>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=status style=width:150px;>".$optstatus."</select></td>
     </tr>";
echo"<tr>
        <td>".$_SESSION['lang']['sukubunga']."</td>
        <td>:</td>
        <td><input type=text id=sukubunga class=myinputtextnumber style=width:90px;> %/Tahun</td>
        <td>".$_SESSION['lang']['jumlahdeposito']."</td>
        <td>:</td>
        <td><input type=text id=jumlahdeposito class=myinputtextnumber onkeyup=\"z.numberFormat('jumlahdeposito',2);\" onkeypress=\"return angka_doang(event);\" style=width:148px;></td>
     </tr>";
echo"<tr>
        <td></td><td></td>
        <td><button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
            <button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
        </td>
     </tr>
     <input type=hidden id=method value='insert'/>
     <input type=hidden id=notransaksi value=''>
     </table>";
echo"</fieldset>"; 
if ($_SESSION['language'] == 'ID') {
echo"<fieldset style='text-align:left;height:95px;width:205px'>
    <legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
    Pastikan Unit Sudah Terdaftar pada menu <b>Keuangan - Setup - Daftar Rek Bank Perusahaan</b>.
    </fieldset>";
}else{
    echo"<fieldset style='text-align:left;height:95px;width:205px'>
    <legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
    Please register unit at <b>Finance - Setup - Daftar Rek Bank Perusahaan</b>.
    </fieldset>";
}
echo"</div>";
CLOSE_BOX();
echo close_body(); ?>

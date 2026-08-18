<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_uangmuka')."</span>"); //1 O
?>
<script language=javascript>
notifpopilih="<?php echo $_SESSION['lang']['notifpopilih']; ?>";
notiftagihtanggal="<?php echo $_SESSION['lang']['notiftagihtanggal']; ?>";
notifpostingpenagihan="<?php echo $_SESSION['lang']['notifpostingpenagihan']; ?>";
</script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/keu_uangmuka.js?v=<?php echo time(); ?>" /></script>

<?php

$optCgttu=$optPenerima=$optrek=$optjenispajak=$optNpwp=$optMtUang=$optUnit=$optSupplier=$optPt=$optJenis=$optJenisUangMuka="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#untuk list PT
$optOrg=array();
$optOrg=getOrgDetail(3);
foreach($optOrg as $row=>$data){
    if($row!=''){
        $optPt.="<option value='".$row."'>".$data."</option>";
    }
}

# ambil unit
$arrUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lstUnit=getOrgDetail(1);
$dtMul=0;
$listOrg='';
foreach($lstUnit as $row=>$isiDt){
    if(substr($row,0,5)=='Pilih'){
        continue;
    }
    if($dtMul==0){
        $listOrg="'".$row."'";
        $dtMul=1;
    }else{
        $listOrg.=",'".$row."'";
    }
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$listOrg.")";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";    
}


$sql = "select supplierid as id,namasupplier as nama from ".$dbname.".log_5supplier where supplierid in(select penerima_id from ".$dbname.".keu_uangmuka)";
$query = fetchData($sql);
foreach ($query as $key=>$value){
    $optPenerima .= "<option value=".$query[$key]['id'].">".$query[$key]['nama']." - ".$query[$key]['id']."</option>";
}

$sql = "select karyawanid as id,namakaryawan as nama from ".$dbname.".datakaryawan where karyawanid in(select penerima_id from ".$dbname.".keu_uangmuka)";
$query = fetchData($sql);
foreach ($query as $key=>$value){
    $optPenerima .= "<option value=".$query[$key]['id'].">".$query[$key]['nama']." - ".$query[$key]['id']."</option>";
}

//exit("Error ".$optPenerima);


#matauang
$str="select * from ".$dbname.".setup_matauang";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optMtUang.="<option value='IDR'>IDR</option>";
while ($bar=$res->fetch()) {
    if ($bar['kode'] != 'IDR') {
         $optMtUang.="<option value='".$bar['kode']."'>".$bar['kode']."</option>";
    }
}

#transaksipajak


$jenisSearch = array(
    'notransaksi' => $_SESSION['lang']['notransaksi'],
    'no_transaksi_ref' => $_SESSION['lang']['noreferensi'],
    // 'namasupplier' => $_SESSION['lang']['supplier'],
    // 'nopo' => $_SESSION['lang']['nopo'],
);

foreach($jenisSearch as $row=>$jns){
    $optjenisSearch.="<option value='".$row."'>".$jns."</option>";
}

echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo"<select id=sJenis style=width:150px;>".$optjenisSearch."</select> &nbsp;";
echo"<input type=text id=sNoTrans class=myinputtext /> &nbsp;";
echo"<select id=Spenerima style=width:150px;>".$optPenerima."</select><img id=Spenerima onclick=z.elSearch('Spenerima',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> &nbsp;";
echo"<button class=mybutton onclick=loadData('search')>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 
CLOSE_BOX();

OPEN_BOX();

#START FORM INPUT TRANSAKSI#

//"tanggal/um/unit/nourut
#generate no transaksi



$defaultSizeCombo = '250';
echo"<div id=formInput style=display:none;>";//style=display:none;
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table border=0 >";
echo"<tr>	
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>
		<td><input type=text id=notransaksi style=width:150px; class=myinputtext disabled></td>
        <td>&nbsp;</td>
       "; 

echo"<tr>  
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td><select id=kodeorg style=width:".$defaultSizeCombo."px; onchange=getunit(this,0,0)>".$optPt."</select>
        <img id='kodeorg' onclick=z.elSearch('kodeorg',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
        <td>&nbsp;</td>
       ";
echo"<tr>  
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style=width:".$defaultSizeCombo."px onchange=generateNoTran('".date('Ymd')."/UM/"."')>".$optUnit."</select></td>
        <td>&nbsp;</td>
        </tr>";

echo"<tr>  
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type=text class=myinputtext id=tanggal readonly=readonly type=text onmousemove=setCalendar(this.id) onkeypress=return false; style=width:150px; maxlength=10 /></td>
        <td>&nbsp;</td>
     <tr>";   


$arrJenisUangMuka = fetchData("select kode,nama_uangmuka from ".$dbname.".keu_5jenisuangmuka");
// echo "<pre>";
// print_r($arrJenisUangMuka);
foreach ($arrJenisUangMuka as $value){
    
    $optJenisUangMuka .= "<option value=".$value['kode'].">".$value['nama_uangmuka']."</option>";
}
//print_r($optJenisUangMuka);


echo"<tr>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id='jenis' onchange='fillNoAkun()' style=width:".$defaultSizeCombo."px>"
         .$optJenisUangMuka.
        "</select></td>
        <td>&nbsp;</td>
        
     </tr>";

echo"<tr>
     <td>".$_SESSION['lang']['noakun']."</td>
     <td>:</td>
     <td><select id=noakun  disabled='yes' style=width:".$defaultSizeCombo."px >".$optNoAkun."</select></td>
     <td>&nbsp;</td>
     
  </tr>";     

echo"<tr>  
  <td>".$_SESSION['lang']['notransaksireferensi']."</td>
  <td>:</td>
  <td><select id='notransaksireferensi' onchange='fillPenerima()' style=width:".$defaultSizeCombo."px>".$optNoRef."</select>
  <img id='notransaksireferensi' onclick=z.elSearch('notransaksireferensi',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>

  </td>
  <td>&nbsp;</td>
  
</tr>";

echo "<tr>
<td>".$_SESSION['lang']['penerima']."</td>
<td>:</td>
<td><select id='penerima' style=width:".$defaultSizeCombo."px disabled=yes></select>
</td>
<td>&nbsp;</td>

";
echo "<tr>
    <td>".$_SESSION['lang']['keterangan']."</td>
    <td>:</td>
    <td><input type=text id=keterangan style=width:".$defaultSizeCombo."px; class=myinputtext onkeypress='return tanpa_kutip(event)'></td><td>&nbsp;</td>
    </tr>";


echo"<tr>  
        <td>".$_SESSION['lang']['nilai']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=nilai onkeyup=\"z.numberFormat('nilai',2);\"  onkeypress=return angka_doang(event); style=width:150px; /></td>
        <td>&nbsp;</td>
      
     </tr>";

// echo"<tr>  
//      <td>".$_SESSION['lang']['cgttu']."</td>
//      <td>:</td>
//      <td><select id=cgttu style=width:".$defaultSizeCombo."px onclick=fillNoRek()>".
//         $optCgttu."
//         <option value=\"Cash\">Cash</option>
//         <option value=\"Transfer\">Transfer</option>
//         <option value=\"Giro\">Giro</option>
//         <option value=\"Cheque\">Cheque</option>
//      </select></td>
//      <td>&nbsp;</td>
   
//   </tr>";

// echo"<tr>  
//   <td>".$_SESSION['lang']['bank']."</td>
//   <td>:</td>
//   <td><input type=text class=myinputtextnumber id=norek style=width:150px; disabled/></td>
//   <td>&nbsp;</td>

// </tr>";

// echo"<tr>  
//   <td>".$_SESSION['lang']['norek']."</td>
//   <td>:</td>
//   <td><input type=text class=myinputtextnumber id=norek style=width:150px; disabled/></td>
//   <td>&nbsp;</td>

// </tr>";
     
echo"<tr>
        
        <td><button class=mybutton onclick=insert() id=aksi>".$_SESSION['lang']['save']."</button>
        <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button></td>
     </tr>
     </table>";
echo "</div>";


echo"<div id=listData>";
echo"<fieldset style=float:left;clear:right><legend><b>".$_SESSION['lang']['print']."</b></legend>";
echo"<img class=\"zImgBtn\" src=\"images/skyblue/print.png\" style=\"cursor:pointer\" onclick=\"print()\" title=\"Print Page\">
<img class=\"zImgBtn\" src=\"images/skyblue/pdf.jpg\" style=\"cursor:pointer\" onclick=\"printPDF(event)\" title=\"Print PDF\">";
# Content
$cols = "notransaksi,unit,tanggal,jenis,noreferensi,penerima,nilai,updateby,keterangan,dipostingoleh";
$listTitle=explode(",",$cols);
echo"</fieldset>";
echo"<fieldset style=clear:left><legend><b>".$_SESSION['lang']['list']."</b></legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100% >";
echo"<thead>";
echo"<tr align=center>";
foreach ($listTitle as $key => $value) {
    echo "<td>".$_SESSION['lang'][$value]."</td>";
}
echo"<td colspan=7>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value='add' />";



CLOSE_BOX();


//CLOSE_BOX();

echo"</fieldset>"; 
echo"</div>";
echo"<div id=detailField style='display:none'>";
echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
echo"<fieldset>";
echo"<table boder=0>";

echo"</table></fieldset>";
echo"</fieldset>"; 
echo"</div>";
echo close_body(); ?>

<?php 
// function test($a,$b=5,$c){

//     echo $a+$b+$c;
// }

// test(5,,2);

?>
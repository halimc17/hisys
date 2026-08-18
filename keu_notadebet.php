<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_notadebet')."</span>"); 
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>

<script language=javascript src='js/keu_notadebet.js?v=<?php echo time(); ?>'></script>

<?php
/*<script type="text/javascript" src="js/keu_notadebet.js" /></script>*/
echo"<table align=middle>
     <tr valign=middle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo $_SESSION['lang']['no_notadebet']." : <input type='text' id=crnotadebet class=myinputtext> ";
echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
     </table> "; 
CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset style=float:left;clear:right><legend><b>".$_SESSION['lang']['print']."</b></legend>";
echo"<img class=\"zImgBtn\" src=\"images/skyblue/print.png\" style=\"cursor:pointer\" onclick=\"print()\" title=\"Print Page\">
<img class=\"zImgBtn\" src=\"images/skyblue/pdf.jpg\" style=\"cursor:pointer\" onclick=\"printPDF(event)\" title=\"Print PDF\">";

# Content
$cols = "no_notadebet,pt,tanggal,jenis,no_notahutang,supplier,keterangan,nilaiinvoice,dipostingoleh";
$listTitle=explode(",",$cols);
echo"</fieldset>";
echo"<fieldset style=clear:left><legend><b>".$_SESSION['lang']['list']."</b></legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100% >";
echo"<thead>";
echo"<tr align=center>";
foreach ($listTitle as $key => $value) {
    echo "<td>".$_SESSION['lang'][$value]."</td>";
}
echo"<td colspan=5>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";

#untuk list PT
$optOrg=array();
$optUnit=array();
$optUnit=$optOrg = array(""=>$_SESSION['lang']['pilihdata']);
$arrtOrg=getOrgDetail(3);
foreach($arrtOrg as $row=>$data){
    $optOrg[$row]=$row." - ".$data;
}

# ambil unit
$lstUnit=getOrgDetail(1);
foreach($lstUnit as $row=>$isiDt){
    $optUnit[$row]=$row." - ".$isiDt;
}

$optJnsInv = makeOption($dbname,'keu_5jenistagihan','kode,namajenis','','',true);
$optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier','','',true);

// $arrTp=array("0"=>"Default","1"=>"Jurnal Audit");
$arrTp=array("0"=>"Default");
foreach($arrTp as $row=>$lst){
    $optTipe.="<option value='".$row."'>".$lst."</option>";
    $arrTipe[$row]=$lst;
}

$optRev=array("0"=>$_SESSION['lang']['pilihdata']);
for ($i = 1; $i <= 5; $i++) {
    $optRev[$i]=$i;
}

$str="select * from ".$dbname.".setup_matauang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optKurs['IDR']="IDR";
while($bar=$res->fetch())
{
    if($bar['kode']!='IDR'){
        @$optKurs[$bar['kode']].=$bar['kode'];
    }
}


#= noakun
// $str="select * from ".$dbname.".keu_5akun where noakun in (select noakun from ".$dbname.".log_5klsupplier)";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $optakun['']=$_SESSION['lang']['pilihdata'];
// while($bar=$res->fetch())
// {
  
        // @$optakun[$bar['noakun']].=$bar['noakun']." [".$bar['namaakun']."]";
// }


$opttipesupplier=$optakun='';

echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend><span id=judulForm><b>".$_SESSION['lang']['addheader']."</b></span></legend>";
echo"<table border=0 cellspacing=1 cellpading=1 style=width:100%>";
echo"<tr>";
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['no_notadebet']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('notadebet','text',@$data['noinvoice'],
        array('style'=>'width:152px','maxlength'=>'20','disabled'=>'disabled'))."</td>";

echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['no_notahutang']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('notahutang','text',@$data['noinvoice_referensi'],
        array('style'=>'width:152px;cursor:pointer','readonly'=>'readonly','placeholder' => 'Click to choose','onclick'=>"searchnodo('".$_SESSION['lang']['find']."','<div id=formPencariandata></div>',event)"))."</td>";
echo"</tr>";

echo"<tr>";
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['transaksi']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('tipe','select',@$data['tipe'],
        array('style'=>'width:155px','onchange'=>'gettgl(this)'),$arrTipe)."</td>";
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['tipeinvoice']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('tipeinvoice','select',@$data['tipeinvoice'],
        array('style'=>'width:155px'),$optJnsInv)."</td>";



echo"</tr>";  

echo"<tr>   
     <td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['tanggal']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('tanggal','text',@$data['tanggal'],array('style'=>'width:152px','readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))."</td>";
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['supplier']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('supplier','selectsearch',@$data['kodesupplier'],
	 array('style'=>'width:155px','onchange'=>'gettipesupplier()'),$optNmsupp)."</td>";
echo"</tr>";

echo"<tr id=revisix style=display:none>"; 
echo"<td  style=padding-right:20px;font-size:12px>".$_SESSION['lang']['revisi']."</td>
     <td>:</td>
     <td  style=padding-right:20px;font-size:12px>".makeElement('revisi','select',@$data['revisi'],
            array('style'=>'width:155px','disabled'=>'disabled'),$optRev)."</td>"; 
     
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['matauang']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('matauang','select',@$data['matauang'],
        array('style'=>'width:155px','disabled'=>'disabled'),$optKurs)."</td>";
echo"</tr>";   

echo"<tr>"; 
echo"<td  style=padding-right:20px;font-size:12px>".$_SESSION['lang']['pt']."</td>
     <td>:</td>
     <td  style=padding-right:20px;font-size:12px>".makeElement('kodeorg','select',@$data['kodeorg'],
            array('style'=>'width:155px','onchange'=>'getunit()'),$optOrg)."</td>"; 
			
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['supplier']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('tipesupplier','select',@$data['tipesupplier'],
        array('style'=>'width:155px','onchange'=>'getakunht()'),$opttipesupplier)."</td>";
/*
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['noakun']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('noakun','text','',
            array('style'=>'width:152px','disabled'=>'disabled'))."</td>";
echo"</tr>"; 
*/

 


echo"<tr>"; 
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['unit']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('unit','select',@$data['unit'],
        array('style'=>'width:155px'),$optUnit)."</td>";
		
		
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['noakun']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('noakun','select',@$data['noakun'],
        array('style'=>'width:155px'),$optakun)."</td>";
echo"</tr>";  
echo"</tr>"; 


echo"<tr>"; 
echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['keterangan']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('keterangan','text',@$data['keterangan'],
        array('style'=>'width:152px'))."</td>";

echo"<td style=padding-right:20px;font-size:12px>".$_SESSION['lang']['kurs']."</td>
     <td>:</td>
     <td style=padding-right:20px;font-size:12px>".makeElement('kurs','textnum','1',
        array('style'=>'width:152px','disabled'=>'disabled'))."</td>";

echo"<td hidden style=padding-right:20px;font-size:12px>".$_SESSION['lang']['nilaiinvoice']."</td>
     <td hidden>:</td>
     <td style=padding-right:20px;font-size:12px;>".makeElement('nilaiinvoice','textnum','0',
            array('style'=>'width:152px;display:none','onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))."</td>";
echo"</tr>"; 

echo"<tr> 
     <td><button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button></td>
     <td colspan=5>&nbsp;</td>
     </tr>"; 
echo"</table>";

echo"</fieldset>"; 
echo"</div>";
CLOSE_BOX();

echo"<div id=formdetail style=display:none;>";
echo "</div>";
echo close_body();
 ?>

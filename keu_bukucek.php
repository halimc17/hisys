<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_bukucek')."</span>"); //1 O
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script type="text/javascript">
notifnoinvoiceafiliasi="<?php echo $_SESSION['lang']['notifnoinvoiceafiliasi']; ?>";
notifkontrak="<?php echo $_SESSION['lang']['notifkontrak']; ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/keu_bukucek.js?v=<?php echo time(); ?>></script>

<?php

#nama PT
$optunit=$optbank=$optpt=$opttipe=$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $sakundbt=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc");
// $sakundbt->setFetchMode(PDO::FETCH_ASSOC);
// while($rakun=  $sakundbt->fetch()){
//     $optunit.="<option value='".$rakun['kodeorganisasi']."'>".$rakun['namaorganisasi']."</option>";
// }

#Tipe Transaksi
$arrtipe=getEnum($dbname,'keu_bukucekht','tipe_buku');
foreach($arrtipe as $kei=>$fal)
{
    $opttipe.="<option value='".$kei."'>".$fal."</option>";
}

#nama PT
// $arrtipe=getOrgDetail(3);
// foreach($arrtipe as $kei=>$fal){
//     $optpt.="<option value='".$kei."'>".$fal."</option>";    
// }

#nama unit
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
    $sBank="select * from ".$dbname.".keu_5akunbank where pemilik='".$kei."'";
    $rBank=fetchData($sBank);
    if(count($rBank)!=0){
        $optunit.="<option value='".$kei."'>".$kei." - ".$fal."</option>";
    }
}
$optstatus.="<option value='0'>".$_SESSION['lang']['nonaktif']."</option>";
$optstatus.="<option value='1'>".$_SESSION['lang']['aktif']."</option>";
$optstatus.="<option value='2'>".$_SESSION['lang']['tutup']."</option>";

echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo"<table>";
				echo"<tr>";
					echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
					echo"<td>:</td>";
					echo"<td> <input type=text id=notransaksicr class=myinputtext style=width:150px;></td></td>";
				
					echo"<td>".$_SESSION['lang']['unit']."</td>";
					echo"<td>:</td>";
					echo"<td><select id=unitcr onchange='getakuncr()' style=width:150px;>".$optunit."</select></td>";
					
					
					
					echo"<td>".$_SESSION['lang']['nourut']." ".$_SESSION['lang']['awal']."</td>";
					echo"<td>:</td>";
					echo"<td><input type=text id=noawalcr class=myinputtext style=width:150px; ></td>";
					
					echo"<td>".$_SESSION['lang']['status']."</td>";
					echo"<td>:</td>";
					echo"<td><select id=statuscr style=width:150px;>".$optstatus."</select></td>";
					
					
				echo"</tr>";
				echo"<tr>";
					echo"<td>".$_SESSION['lang']['tipetransaksi']."</td>";
					echo"<td>:</td>";
					echo"<td><select id=tipecr style=width:150px;>".$opttipe."</select></td>";
					
					echo"<td>".$_SESSION['lang']['noakun']."</td>";
					echo"<td>:</td>";
					echo"<td><select id=noakuncr style=width:150px;>".$optbank."</select></td></td>";
					
					echo"<td>".$_SESSION['lang']['nourut']." ".$_SESSION['lang']['akhir']."</td>";
					echo"<td>:</td>";
					echo"<td><input type=text id=noakhircr class=myinputtext style=width:150px; ></td>";
				echo"<tr>";
				echo"</tr>";
				
					echo"<td></td>";
					echo"<td></td>";
					echo"<td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>";
				echo"</tr>";
				
				
					
				
			echo"</table>";
		 
echo"</fieldset></td>
     </tr>
         </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
// echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['kodeorg']."</td>";
echo"<td>".$_SESSION['lang']['tipetransaksi']."</td>";
echo"<td>".$_SESSION['lang']['nama']." Bank</td>";
echo"<td>".$_SESSION['lang']['nourut']." Buku</td>";
echo"<td>".$_SESSION['lang']['dibuatoleh']."</td>";
echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td colspan=5>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table>";
echo"</div><input type=hidden id=proses value=insert />";


echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table border=0 >"; 
echo"</tr>	
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id=unit style=width:230px; onchange='getakun()'>".$optunit."</select></td>
	 </tr>"; 
echo"</tr>  
        <td>".$_SESSION['lang']['namabank']."</td>
        <td>:</td>
        <td><select id=noakun style=width:230px;>".$optbank."</select></td>
     </tr>";
echo"</tr>  
        <td>".$_SESSION['lang']['tipe']." Buku</td>
        <td>:</td>
        <td><select id=tipetransaksi style=width:230px;>".$opttipe."</select></td>
     </tr>";
echo"<tr>
		<td>".$_SESSION['lang']['nourut']." Cek/Giro/PO</td><td>:</td>
		<td><input type=text id=noawal class=myinputtext style=width:100px; > s/d
            <input type=text id=noakhir class=myinputtext style=width:100px; ></td>
	 </tr>";
echo"<tr>
        <td></td><td></td>
        <td><button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
            <button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
        </td>
     </tr>
     <input type=hidden id=method value='insert'/>
     <input type=hidden id=notrans_cek value=''>
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

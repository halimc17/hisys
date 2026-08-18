<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/keu_5daftarperkiraan.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src=js/zTools.js></script>
<?php
// make option untuk menampilkan nama pilihannya di form
$where = "`tipe`='HOLDING' and length(kodeorganisasi)=3";
$optCurr = makeOption($dbname,'setup_matauang','kode,matauang');
//ganti pake yg ini. kalo biar nyambung setelah sama dengan dikasih titik
$optTipeAkun = "<option value= 'Aktiva'>".$_SESSION['lang']['aktiva']. "</option>";
$optTipeAkun.= "<option value= 'Passiva'>Passiva</option>";
$optTipeAkun.= "<option value= 'Modal'>".$_SESSION['lang']['modal']. "</option>";
$optTipeAkun.= "<option value= 'Penjualan'>".$_SESSION['lang']['penjualan']. "</option>";
$optTipeAkun.= "<option value= 'Biaya'>".$_SESSION['lang']['biaya']. "</option>";
$optTipeAkun.= "<option value= 'Lain-lain'>".$_SESSION['lang']['lain']. "</option>";
// $optPemilik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPemilik="";
//pake yang ini
$optlevel = "<option value= '1'> 1 </option>";
$optlevel.= "<option value= '2'> 2 </option>";
$optlevel.= "<option value= '3'> 3 </option>";
$optlevel.= "<option value= '4'> 4 </option>";
$optlevel.= "<option value= '5'> 5 </option>";
$optOrgpemilik=array('GLOBAL'=>'GLOBAL','HOLDING'=>'HOLDING','KANWIL'=>'KANWIL','KEBUN'=>'KEBUN','PABRIK'=>'PABRIK');
foreach ($optOrgpemilik as $key) {
 @$optPemilik.="<option value= '".$key."'>".$key. "</option>";
}
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//fetch obj untuk dijadikan object
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
   $optPemilik.= "<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=3 and tipe='HOLDING' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//fetch obj untuk dijadikan object
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
   $optholding.= "<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
$str1=$owlPDO->query("select kode,matauang from ".$dbname.".setup_matauang 
      order by matauang");
$str1->setFetchMode(PDO::FETCH_OBJ);
$optCurr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str1->fetch()){
    $optCurr.="<option value='".$bar->kode."'>".$bar->matauang."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('keu_5daftarperkiraan').'</span></br>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset>";
    echo"<legend>".$_SESSION['lang']['form'] ."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
                <tr>
                    <!--<td>".$_SESSION['lang']['kodeorg']."</td>
                    <td>:</td>
                    <td><select style='width:200px;display:none' id=kodeorg>".$optholding."</select></td>-->

                    <td>".$_SESSION['lang']['noakun']."</td> 
                    <td>:</td>
                    <td><input type=text  id=noakun onkeypress=\"return angka_doang(event);\"   class=myinputtextnumber style=\"width:195px;\">
                    <select style='width:200px;display:none' id=kodeorg>".$optholding."</select></td>
                    
                    <td>".$_SESSION['lang']['level']."</td>
                    <td>:</td>
                    <td colspan=4><select id=level style=\"width:200px;\">".$optlevel."</select></td>
                    
                    <td>".$_SESSION['lang']['kasbank']." ".$_SESSION['lang']['detail']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kasbankdetail></td>
                    
                    <td>".$_SESSION['lang']['kodekegiatan']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kodekegiatan></td>
                    
                    <td>".$_SESSION['lang']['kodeblok']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kodeblok></td>
                    
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['namaakun']."</td> 
                    <td>:</td>
                    <td hidden><input type=hidden id=namaakunori onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:195px;\">
                    <td><input type=text  id=namaakun onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"return tanpa_kutip(event);\"  class=myinputtext style=\"width:195px;\">
                    </td>
                    
                    <td>".$_SESSION['lang']['matauang']."</td>
                    <td>:</td>
                    <td colspan=4><select id=matauang style=\"width:200px;\">".$optCurr."</select></td>
                    
                    <td>".$_SESSION['lang']['invoice']." AP</td>
                    <td>:</td>
                    <td><input type=checkbox id=tagihan></td>
                    
                    <td>".$_SESSION['lang']['kodeasset']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kodeasset></td>
                    
                    <td>".$_SESSION['lang']['kodesupplier']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kodesupplier></td>
                    
                    
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['namaakun']." (EN)</td> 
                    <td>:</td>
                    <td hidden><input type=hidden id=namaakun1ori onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\"return tanpa_kutip(event);\" class=myinputtext style=\"width:195px;\"></td>
                    <td><input type=text  id=namaakun1 onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:195px;\"></td>
                    
                    <td>".$_SESSION['lang']['pemilik']."</td>
                    <td>:</td>
                    <td colspan=4><select id=pemilik style=\"width:200px;\">".$optPemilik."</select></td>
                    
                    <td>".$_SESSION['lang']['jurnalmemo']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=jurnalmemorial></td>
                    
                    <td>".$_SESSION['lang']['nik']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=nik></td>
                    
                    <td>".$_SESSION['lang']['kodevhc']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kodevhc></td>
                    
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipeakun']."</td>
                    <td>:</td>
                    <td><select id=tipeakun style=\"width:200px;\">".$optTipeAkun."</select></td>
                    
					<td></td>
					<td></td>
					<td></td>
					
                    <td>".$_SESSION['lang']['detail']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=detail></td>
					
					
                    <td>".$_SESSION['lang']['kasbank']." ".$_SESSION['lang']['header']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kasbank></td>
                    
                    <td>".$_SESSION['lang']['nodok']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=nodok></td>
                    
                    <td>".$_SESSION['lang']['kodecustomer']."</td>
                    <td>:</td>
                    <td><input type=checkbox id=kodecustomer></td>
                </tr>";
            echo"<tr><td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=cancel()>Reset</button>
                    </td>
                </tr>
            </table>  
        </fieldset>
        <input type=hidden id=method value='insert'>";
CLOSE_BOX();
OPEN_BOX();
echo "<fieldset style=float:left;>
        <legend>".$_SESSION['lang']['find']."</legend>"; 
      echo "<table><tr>
      <td>".$_SESSION['lang']['noakun']."</td> 
      <td>:</td> 
      <td><input type=text id=txtNoakun size=25 maxlength=30 class=myinputtext></td>";
      echo "
      <td>".$_SESSION['lang']['namaakun']."</td>
      <td>:</td> 
      <td><input type=text id=txtsearch size=25 maxlength=30 class=myinputtext></td>
	  <td> <button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button><button class=mybutton onclick=cancelsearch()>Cancel</button></td></td>
	  
	  
	  </tr>";
      // echo"<tr><td></td><td></td><td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button> 
     
      // </tr>";
echo"</table></fieldset></td>
     </tr><div style=clear:both></div>
        <div id=container class=table-scroll style='height:50vh'>
        </div> <script>loadData(0)</script>
    </fieldset>";
CLOSE_BOX();
echo "</div>";
?>
<?php echo close_body(); ?>
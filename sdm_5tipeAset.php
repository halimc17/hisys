<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/zMysql.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/tipeasset.js></script>
<?
include('master_mainMenu.php');
//ambil akun penyusutan
$str="select noakundebet as noakun,
     keterangan as namaakun from ".$dbname.".keu_5parameterjurnal 
     where kodeaplikasi='DEP' order by keterangan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$optAkun="<option value=''></option>";
if($numrows<1)
{
    echo "Error: Journal parameter for `DEP` not exist";
}
 else {
while($bar=$res->fetch())
{
        $optAkun.="<option value='".$bar->noakun."'>[".$bar->noakun."]-".$bar->namaakun."</option>";
}    
}
if($_SESSION['language']=='EN'){
    $zz="namaakun1 as namaakun";
}else{
    $zz="namaakun";
}
$str="select noakun,".$zz." from ".$dbname.".keu_5akun where length(noakun) = 7";
$optAkunak="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
    $optAkunak.="<option value='".$bar->noakun."'>[".$bar->noakun."]-".$bar->namaakun."</option>";
}

$optTipeDep = getEnum($dbname,'sdm_5tipeasset','metodepenyusutan');
$tipeDep = "";
foreach($optTipeDep as $key=>$val) {
        $tipeDep .= "<option value='".$val."'>".ucfirst($val)."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5tipeAset').'</span>');

echo"<fieldset ><table>
     <tr><td>".$_SESSION['lang']['kode']."</td><td>:</td><td><input type=text id=kodetipe size=4 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
         <tr><td>".$_SESSION['lang']['kode']." Lama</td><td>:</td><td><input type=text id=kodetipelama style=width:262px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
         <tr><td>".$_SESSION['lang']['namakelompok']."</td><td>:</td><td><input type=text id=namatipe style=width:262px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
         <tr><td>".$_SESSION['lang']['namakelompok']."(EN)</td><td>:</td><td><input type=text id=namatipe1 style=width:262px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>             
         <tr hidden><td>".$_SESSION['lang']['namaakun']."</td><td>:</td><td><select style=width:267px id=noakun>".$optAkun."</select></td></tr>
     <tr><td>".$_SESSION['lang']['aktivadalam']."</td><td>:</td><td><select style=width:267px id=noakunak>".$optAkunak."</select></td></tr>
         <tr><td>".$_SESSION['lang']['depmethode']."</td><td>:</td><td><select id=tppenyusutan>".$tipeDep."</select></td></tr>
     
		<tr><td><td><td>
         <input type=hidden id=method value='insert'>
         <button class=mybutton onclick=simpanTipeAset()>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelTipeAsset()>".$_SESSION['lang']['cancel']."</button>
         </table></fieldset>";
CLOSE_BOX();
OPEN_BOX();//<td>".$_SESSION['lang']['namaakun']."</td>    <td>".$bar1->noakun." - ".@$namaakun[$bar1->noakun]."</td>
echo open_theme($_SESSION['lang']['availvhc']);
echo "<div>";
        echo"<table class=sortable cellspacing=1 border=0 >
             <thead>
                 <tr class=rowheader>
                 <td >".$_SESSION['lang']['kode']."</td>
                 <td >".$_SESSION['lang']['kode']." Lama</td>
                 <td>".$_SESSION['lang']['namakelompok']."</td>
         <td>".$_SESSION['lang']['namakelompok']."(EN)</td>
         <td>".$_SESSION['lang']['aktivadalam']."</td>
                 <td>".$_SESSION['lang']['depmethode']."</td>
                 <td style='width:30px;'>Action</td></tr>
                 </thead>
                 <tbody id=container>";
        $str1="select * from ".$dbname.".sdm_5tipeasset 
                   order by namatipe";
				   
        $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar1=$res->fetch())
       {
                       echo"<tr class=rowcontent>
                     <td align=center>".$bar1->kodetipe."</td>
                     <td align=center>".$bar1->kodetipelama."</td>
                         <td>".$bar1->namatipe."</td>
             <td>".$bar1->namatipe1."</td>
             <td>".$bar1->akunak." - ".@$namaakun[$bar1->akunak]."</td>
                         <td>".ucfirst($bar1->metodepenyusutan)."</td>
                         <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodetipe."','".$bar1->namatipe."','".$bar1->namatipe1."','".$bar1->noakun."','".$bar1->akunak."','".$bar1->metodepenyusutan."','".$bar1->kodetipelama."');\"></td></tr>";
        }
		
        echo"	 
                 </tbody>
                 <tfoot>
                 </tfoot>
                 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>
<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/pabrik_5grading.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5grading').'</span>');
$tipenya = getEnum($dbname,"pabrik_5fraksi","kode");
$no = 0;
$opttipe .= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($tipenya as $val){
	$opttipe .= "<option value='".$val."'>".ucwords($val)."</option>";
}
echo"<fieldset style='width:500px;'><table>
     <tr><td>".$_SESSION['lang']['tipe']."</td><td> : </td><td><select id=kode  style=\"width:100px;\">'".$opttipe."'</select></td></tr>
	 <tr><td>".$_SESSION['lang']['nama']."</td><td> : </td><td><input type=text id=nama size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
                    <tr><td>".$_SESSION['lang']['nama']." (EN)</td><td> : </td><td><input type=text id=nama1 size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
     <tr><td>".$_SESSION['lang']['satuan']."</td><td> : </td><td><input type=text id=satuan size=3 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
	 <tr><td><td><td>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanJabatan()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelJabatan()>".$_SESSION['lang']['cancel']."</button>
	 </table></fieldset>";
echo open_theme($_SESSION['lang']['list']);
echo "<div>";
	$str1="select * from ".$dbname.".pabrik_5fraksi order by kode";
	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader><td style='width:50px;'>".$_SESSION['lang']['kodeabs']."</td><td>".$_SESSION['lang']['nama']."</td><td>".$_SESSION['lang']['nama']."(EN)</td><td>".$_SESSION['lang']['satuan']."</td><td style='width:30px;'>Action</td></tr>
		 </thead>
		 <tbody id=container>";
		if(count(fetchData($str1))>0){
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch()){
				echo"<tr class=rowcontent><td align=center>".$bar1->kode."</td><td>".$bar1->keterangan."</td><td>".$bar1->keterangan1."</td><td align=center>".$bar1->type."</td><td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->keterangan."','".$bar1->type."','".$bar1->keterangan1."');\"></td></tr>";
			}	 
		}else{
			echo"<tr class=rowcontent><td colspan=5 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
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
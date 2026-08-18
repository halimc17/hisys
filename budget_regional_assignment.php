<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript1.2 src='js/budget_regional_assignment.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('budget_regional_assignment').'</span><br>');
//ambil organisasi 
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi  where char_length(kodeorganisasi) = 4 order by induk, kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$key = $bar->kodeorganisasi;
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
    $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

//ambil regional 
$optreg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select regional, nama from ".$dbname.".bgt_regional  order by regional";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optreg.="<option value='".$bar->regional."'>".$bar->regional." - ".$bar->nama."</option>";	
}


echo"<fieldset style='float:left;'><table>
     <tr>
		<td>".$_SESSION['lang']['kodeorganisasi']."</td>
		<td><select class=select2 id=organisasi style='width:200px'>".$optorg."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['regional']."</td>
		<td><select class=select2 id=regional style='width:200px'>".$optreg."</select></td>
	</tr>
	<tr>
		<td>Sub ".$_SESSION['lang']['regional']."</td>
		<td><input class=myinputtext onkeydown=\"upperCaseF(this)\" id=subregional style='width:195px;'></td>
	</tr>
	 
	 <tr>
		<td></td>
		<td>		
			 <input type=hidden id=method value='insert'>
			 <button class=mybutton onclick=simpanDep()>".$_SESSION['lang']['save']."</button>
			 <button class=mybutton onclick=cancelDep()>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
	 
     </table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo open_theme($_SESSION['lang']['datatersimpan']);

echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
     <thead>
         <tr class=rowheader>
			<th>No</th>
			<th>".$_SESSION['lang']['kodeorganisasi']."</th>
			<th>".$_SESSION['lang']['regional']."</th>
			<th>Sub ".$_SESSION['lang']['regional']."</th>
			<th style='width:30px;'>Action</th>
			</tr>
         </thead>
         <tbody id=container>";
		$str1="select * from ".$dbname.".bgt_regional_assignment a left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi order by induk, regional, kodeunit";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res1->fetch()){
			$key = $bar1->kodeorganisasi;
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				echo"<tr class=rowcontent>
					<td colspan=5><b>".getNamaOrg($d)."</b></td>
				</tr>";
			}
			$no++;
			echo"<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$bar1->kodeunit." - ".getNamaOrg($bar1->kodeunit)."</td>
				<td>".$bar1->regional."</td>
				<td>".$bar1->subregional."</td>
				<td align=center><img src=images/application/application_delete.png class=resicon  caption='Edit' onclick=\"deleteDep('".$bar1->kodeunit."','".$bar1->regional."','".$bar1->subregional."');\"></td>
			</tr>";
			$n=$d;
		}	 
echo"	 
         </tbody>
         <tfoot>
         </tfoot>
         </table>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>
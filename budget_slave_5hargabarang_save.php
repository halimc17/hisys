<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$what       =$_POST['what'];
$tahunbudget=$_POST['tahunbudget'];
$regional   =$_POST['regional'];
$sumberharga=$_POST['sumberharga'];
$kodebarang =$_POST['kodebarang'];
$hargasatuan=$_POST['hargasatuan'];
$variant    =$_POST['variant'];
$hargalalu  =$_POST['hargalalu'];

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

if($what=='revisiharga'){
	echo"<table><tr>";
    echo "<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td>
			  <td id=tahunbudget1x>".$param['tahunbudget']."</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['regional']."</td><td>:</td>
			<td id=regional1x>".$param['regional']."</td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodebarang']."</td><td>:</td>
			<td id=kodebarang1x>".$param['kodebarang']."</td>
		</tr>
			<td>".$_SESSION['lang']['namabarang']."</td><td>:</td>
			<td id=namabarang1x>".$param['namabarang']."</td>
		<tr>
		</tr>
			<td>".$_SESSION['lang']['satuan']."</td><td>:</td>
			<td id=satuan1x>".$param['satuan']."</td>
		<tr>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['hargasatuan']."</td><td>:</td>
			<td><input type=text id=hargasatuan1x size=10 maxlength=10 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value='".$param['hargasatuan']."'></td>
		</tr>
		<tr>
			<td></td><td></td>
			<td><button id=buttonedit class=mybutton onclick=editHargax()>".$_SESSION['lang']['save']."</button>
			<input type=\"hidden\" id=\"hiddenedit\" name=\"hiddenedit\" value=\"\" />
			</td>
		</tr>
		</table>";
}

if($what=='update'){
    $str="UPDATE ".$dbname.".bgt_masterbarang SET `hargasatuan` = '".$hargasatuan."', `sumberharga` = '".$sumberharga."',
        `hargalalu` = '".$hargalalu."', `variant` = '".$variant."', `updateby` = '".$_SESSION['standard']['userid']."', 
        `lastupdate` = CURRENT_TIMESTAMP 
        WHERE `regional` = '".$regional."' AND `tahunbudget` = '".$tahunbudget."' AND `kodebarang` = '".$kodebarang."'";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal 9  !: " . $e->getMessage() . "\n"; die(); }	
}else if($what=='edit'){
    $tutupdata=1;
    $str="select * from ".$dbname.".bgt_masterbarang where "
         . "tahunbudget='".$tahunbudget."' and regional='".$regional."' "
         . "and kodebarang='".$kodebarang."' and hargasatuan>=0";
    $adadata=false;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $adadata=true;	
        $tutupdata=$bar->closed;	
    }

    if($adadata==true){
        $sCek="select * from ".$dbname.".bgt_budget where kodebarang in (select kodebarang from ".$dbname.".bgt_masterbarang where `regional` = '".$regional."' AND `tahunbudget` = '".$tahunbudget."' AND `kodebarang` = '".$kodebarang."') and tahunbudget='".$tahunbudget."'";
        $rCek=fetchData($sCek);
            
		$str="UPDATE ".$dbname.".bgt_masterbarang SET `hargasatuan` = '".$hargasatuan."',
		`updateby` = '".$_SESSION['standard']['userid']."', 
		`lastupdate` = CURRENT_TIMESTAMP, closed='".$tutupdata."' 
		WHERE `regional` = '".$regional."' AND `tahunbudget` = '".$tahunbudget."' AND `kodebarang` = '".$kodebarang."'";
        
		if(count($rCek)==0){
        }else{
            #exit('warning: Material Sudah digunakan tidak bisa diupdate harganya');
        }
       
    }else if($adadata==false){
        $str="INSERT INTO ".$dbname.".`bgt_masterbarang` (`regional` ,`tahunbudget` ,`kodebarang` ,`hargasatuan` ,`updateby` ,`closed` ,`lastupdate`)
        VALUES ('".$regional."', '".$tahunbudget."', '".$kodebarang."', '".$hargasatuan."', '".$_SESSION['standard']['userid']."', '".$tutupdata."',CURRENT_TIMESTAMP )";
    }
	
	
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal 10  !: Allowed only to zero price material\n" . $e->getMessage() . "\n"; die(); }	
}else{
	$tutupdata=0;
	$str="select * from ".$dbname.".bgt_masterbarang where tahunbudget='".$tahunbudget."' and regional='".$regional."' and closed>0";
	$res=fetchData($str);
    foreach($res as $bar){
        $tutupdata=$bar['closed'];	
    }
	
    $str="DELETE FROM ".$dbname.".bgt_masterbarang WHERE tahunbudget='".$tahunbudget."' AND regional='".$regional."' AND kodebarang='".$kodebarang."'";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal 11 !: " . $e->getMessage() . "\n"; die(); }	

    $str="INSERT INTO ".$dbname.".`bgt_masterbarang` (`regional` ,`tahunbudget` ,`kodebarang` ,`hargasatuan` ,`sumberharga` ,`variant` ,`updateby` ,`lastupdate` ,`hargalalu`,`closed`)
	VALUES ('".$regional."', '".$tahunbudget."', '".$kodebarang."', '".$hargasatuan."', '".$sumberharga."' , '".$variant."', '".$_SESSION['standard']['userid']."',CURRENT_TIMESTAMP , '".$hargalalu."','".$tutupdata."')";
	
   try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal 12 !: " . $e->getMessage() . "\n"; die(); }
}
?>
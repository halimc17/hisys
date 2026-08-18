<?php
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('../master_validation.php');
OPEN_BODY_NEWBI();
?>
<link rel=stylesheet type=text/css href=style/graph.css>
<link rel=stylesheet type=text/css href=style/styles.css>
<script  type="text/javascript" src="js/menu.js"></script>
<script  type="text/javascript" src="js/graph.js"></script>

<?php


$optPT = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str = "select * from ".$dbname.".organisasi where tipe = 'PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optPT .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
$optthn='';
// $tahunskrg=date('Y');
// $sepuluhthnlalu=$tahunskrg-10;
// for($i=$tahunskrg;$i>=$sepuluhthnlalu;$i--){
// 	$optthn .= "<option value='".$i."'>".$i."</option>";
// }



$str = "select distinct left(periode,4) as tahun from ".$dbname.".setup_periodeakuntansi order by left(periode,4) desc limit 10";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$optthn .= "<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}

$optpks = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#####################################################################################################
#####################################################################################################


//OPEN_BOX('','<b>OWL PLANTATION GRAPH REPORT</b> <img style=cursor:pointer class=ressicon onclick=showoptionx() src=images/menuBtn.png class=iconmenu>');
OPEN_BOX(); 




echo "<div id='menu_map' title='View Menu' onclick='showoption()'>
	<img src='./images/menuBtn.png'>
</div>";
echo"<div id='menumap' style='display:none;border-right:#999999 solid 3px;'>
<div id=header style='padding-top:15px;padding-bottom:15px;padding-left:10px'>
	<b>OWL Plantation Graph</b>
	<span style='float:right;margin-right:5px;cursor:pointer' title='Hidden Menu' onclick='hideoption()'><img src='images/36.png'></span>
</div>
<hr>";

$frm[0]="";
$frm[0].="<div style=overflow:auto;height:300px><table >";
		
$frm[0].="<tr>
		<td  bgcolor=#A9A9A9 style=width:50px;height:30px align=center>
		<img src='images\download.png' class=iconmenukiri onclick=homegraph()></td>
		
		<td bgcolor=#A9A9A9 style=\"width:200px;cursor:pointer;color:#F8F8FF;height:30px\" 
		title='Main Menu' align=left onclick=homegraph()><b>&nbsp; Main Menu</b></td>
		</tr>";
		
$str="select * from ".$dbname.".bi_5menugraph where tipe=0";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['icon']=='')
	{
		$bar['icon']='images/menugraph/owl.png';
	}
	$sRow="select count(id) as rowdt from ".$dbname.".bi_5menugraph where induk='".$bar['id']."'";
	$qRow=$owlPDO->query($sRow) or die(print " Gagal: ".PDOException::getMessage());
	$qRow->setFetchMode(PDO::FETCH_ASSOC);
	$rRow=$qRow->fetch();
	$frm[0].="
		<tr>
			<td bgcolor=#87CEFA style=width:50px;height:30px align=center title='".ucwords($bar['caption'])."'>
			<img src=".$bar['icon']." class=iconmenukiri onclick=getmenu(".$bar['id'].",".$rRow['rowdt'].")>
			</td>
			
			<td bgcolor=#87CEFA style=\"width:200px;cursor:pointer;color:#F8F8FF\" title='".ucwords($bar['caption'])."'  
			align=left onclick=getmenu(".$bar['id'].",".$rRow['rowdt'].")>
			
			<b>&nbsp; ".ucwords($bar['caption'])."</b>
			</td>
		</tr>";	
		//<fieldset style='cursor:pointer;' title='".$bar['caption']."'></fieldset>
}
//$frm[0].="<tr><td align=center colspan=4><button onclick=homegraph() class=mybutton>Main Menu</button></td></tr>";
$frm[0].="</table></div>";
// $frm[1] .= "<div id='detailpt'>";
$hfrm[0] = $_SESSION['lang']['menu'];

drawaccordion($hfrm,$frm);
echo "</div>";



$form="
	<table style=width:50%>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id='pt' onchange=getpks(); style=\"width:150px;\">".$optPT."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tahun']."</td>
			<td>:</td>
			<td><select id='thn' onchange=getmenu() style=\"width:150px;\">".$optthn."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['pabrik']."</td>
			<td>:</td>
			<td><select id='pks' onchange=getmenu() disabled style=\"width:150px;\">".$optpks."</select></td>
		</tr>
		<tr hidden><td colspan=2></td>
			<td align=left><button onclick=homegraph()>Main Menu</button></td>
		</tr>
	</table>";
echo "<div id='addons' style='display:none'>";
echo showpopup('Option',$form,'addons','pane');
echo "</div>";

echo "<div style='min-height:620px;max-height:100%'>
	<fieldset style='min-height:620px;max-height:100%;width:100%;border:orange solid 1px;background-color:#99CCFF;overflow: auto;'>";
	#content disini

	#head disini
	echo"<div id=head  style='display:block'>";
	$col=array("1"=>"grad1","2"=>"grad2","3"=>"grad3","4"=>"grad4","5"=>"grad5","6"=>"grad6","7"=>"grad7","8"=>"grad8");
	$str="select * from ".$dbname.".bi_5menugraph where tipe=0 order by caption asc ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$menuht[$bar['id']]=$bar['id'];
		$capht[$bar['id']]=$bar['caption'];
		$icon[$bar['id']]=$bar['icon'];
		$no+=1;
		if($bar['icon']!=''){
			$logo="<img src='".$bar['icon']."' title='".strtoupper($bar['caption'])."' class=iconmenu>";
		}
		else{
			$logo="<img src='images/menugraph/owl.png'  title='".strtoupper($bar['caption'])."' class=iconmenu>";
		}
		//@$div='thumbnailfront tile tile-wide tile-'.$col[$no];
		@$div='thumbnailfront tile tile-wide ';
		echo"
		<div class='col-sm-4 col-md-3'>
			<div class='".$div."' onclick='getmenu(".$bar['id'].")' title='".ucwords($_SESSION['lang'][$bar['caption']])."'>
				<br><br>".$logo."
				<br><br>
					<h2 align=center><b>".ucwords($_SESSION['lang'][$bar['caption']])."</b></h2>
			</div>
		</div>";
	}
	echo"</div>";
	echo"<div id=foot  style='display:none'>";
	echo"<div id=menudt></div>";
	echo"</div>";
echo"</fieldset></div>";

CLOSE_BOX();

CLOSE_BODY_NEWBI();
?>
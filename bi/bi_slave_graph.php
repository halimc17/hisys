<?
require_once('master_validation.php');
include('../config/connection.php');
include('../lib/nangkoelib.php');


$idmenu = checkPostGet('idmenu', '');
$method = checkPostGet('method', '');
$pt = checkPostGet('pt', '');
$thn = checkPostGet('thn', '');
$pks = checkPostGet('pks', '');


if($thn=='')
{
	$thn=date('Y');
}

switch ($method) {
	
	case'getmenu':
		
		echo"<input type=text id=idmenu hidden disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">";
		$str="select distinct(kelompok) as kelompok from ".$dbname.".bi_5menugraph where tipe=1 and induk='".$idmenu."' order by kelompok asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		if($row==0){
			echo"<br><h2 align=center>".$_SESSION['lang']['dataempty']."</h2>";
		}
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			echo"<fieldset border=1><legend><font size=3><b>".strtoupper($_SESSION['lang'][$bar['kelompok']])."</b></font></legend>";
			$str1="select * from ".$dbname.".bi_5menugraph where tipe=1 and induk='".$idmenu."' and kelompok='".$bar['kelompok']."' order by  caption asc ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			//<td align=right style='width:10px'><img tittle=".$_SESSION['lang']['tahun']." onclick=javascript:history.back(-1) src=images/bullet_arrow_down.png class=iconmenudetail></td>
			//<a href=javascript:history.back(-1)>Back</a> 
			while($bar1=$res1->fetch()){
				echo"
				<div class='col-sm-1100 col-md-6'>
					<div class='thumbnail tile tile-large tile-clouds'>
						<table border=0 cellspacing=1 cellpadding=1>
							<tr>
								<td align=center width:40px><font size=2><b>".strtoupper($_SESSION['lang'][$bar1['caption']])."</font></b></td>
								<td align=left style='width:10px'><img onclick=\"detailgraph('".$bar1['id']."','".$bar1['file']."','event','".strtoupper($_SESSION['lang'][$bar1['caption']])."')\" src='images/Menu 2 Filled-100.png' class=iconmenudetail></td>
							</tr>
							<tr>
								<td colspan=3 align=center>
								<iframe align=center style=border:none src='".$bar1['file']."?method=global&id=".$bar1['id']."&pt=".$pt."&thn=".$thn."&pks=".$pks."' width=600px height=300px></iframe>
								</td>
							</tr>
						</table>
							
						</div>
					</div>";
			}
			echo"</fieldset>";
		}
		
			
		
	break;


	case'getpks':
		$optpks = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$pt."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optpks .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
		}
		echo $optpks;
	break;
	
}
?>
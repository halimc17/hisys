<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');

/*	print"<pre>";
	print_r($_GET);
	print"<pre>";*/
	
//======================================

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$namapt=strtoupper($bar->namaorganisasi);
}
	
			//echo"warning:masuk vvv";
			$period=date("Y-m");
			$strx="select * from ".$dbname.".kebun_pengirimanbbt where tanggal like '%".$period."%' order by tanggal desc";
		//echo"warning:".$strx;exit();
			$stream.="
			<table>
			<tr><td colspan=7 align=center>".$_SESSION['lang']['pengirimanBibit']."</td></tr>
			<tr><td colspan=3>&nbsp;</td></tr>
			</table>
			<table border=1>
						<tr>
							<td bgcolor=#DEDEDE align=center>No.</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaorganisasi']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nmcust']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['OrgTujuan']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jenisbibit']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakegiatan']."</td>							  
						</tr>";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($resx);
		if($row<1){
			$stream.="	<tr class=rowcontent>
			<td colspan=8 align=center>Not Avaliable</td></tr>
			";
		}
		else
		{
			$no=0;
			while($barx=$resx->fetch())
			{
				$no+=1;
				$sKdOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$barx['kodeorg']."'";
				$qKdOrg=$owlPDO->query($sKdOrg) or die(print " Gagal: ".PDOException::getMessage());
				$qKdOrg->setFetchMode(PDO::FETCH_ASSOC);
				$rKdOrg=$qKdOrg->fetch();
				
				$sKeg="select kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kodekegiatan='".$barx['kodekegiatan']."'";
				$qKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
				$qKeg->setFetchMode(PDO::FETCH_ASSOC);
				$rKeg=$qKeg->fetch();
				
				$sCust="select namacustomer from ".$dbname.".pmn_4customer where kodecustomer='".$barx['pembeliluar']."'";
				$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
				$qCust->setFetchMode(PDO::FETCH_ASSOC);
				$rCust=$qCust->fetch();
				
				$sKdOrg2="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$barx['orgtujuan']."'";
				$qKdOrg2=$owlPDO->query($sKdOrg2) or die(print " Gagal: ".PDOException::getMessage());
				$qKdOrg2->setFetchMode(PDO::FETCH_ASSOC);
				$rKdOrg2=$qKdOrg2->fetch();
				
				$stream.="	<tr class=rowcontent>
							<td>".$no."</td>
							<td>".$barx['notransaksi']."</td>
							<td>".$rKdOrg['namaorganisasi']."</td>
							<td>".$rCust['namacustomer']."</td>
							<td>".$rKdOrg2['namaorganisasi']."</td>
							<td>".tanggalnormal($barx['tanggal'])."</td>
							<td>".$barx['jenisbibit']."</td>
							<td>".$barx['jumlah']."</td>	
							<td>".$rKeg['kelompok']."-".$rKeg['namakegiatan']."</td>	
					</tr>";
			}
		}
	
	//echo "warning:".$strx;
//=================================================
		
	$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

$nop_="PengirimanBibit";
if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != "index.html") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
 }
fclose($handle);
}
?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');


//======================================
$query = selectQuery($dbname,'organisasi','alamat,telepon',
	"kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
$orgData = fetchData($query);
  	
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
$namapt='COMPANY NAME';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$namapt=strtoupper($bar->namaorganisasi);
}
	
			$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
			$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_OBJ);			
			$rOrg=$qOrg->fetch();	
			//echo"warning:masuk vvv";
			$strx="select * from ".$dbname.".kebun_rekomendasipupuk where substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by periodepemupukan asc";
		//echo"warning:".$strx;exit();
			$stream="
			<table>
			<tr><td colspan=9 align=center>".$_SESSION['lang']['rekomendasiPupuk']."</td></tr>
			<tr><td colspan=3>".$_SESSION['lang']['kebun']."</td><td colspan=3 align=left>".$rOrg['namaorganisasi']."</td></tr>
			</table>
			<table border=1>
						<tr>
						  <td bgcolor=#DEDEDE align=center>No.</td>
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tahunpupuk']."</td>
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['afdeling']."</td>
						   <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['blok']."</td>
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tahuntanam']."</td>
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jenisPupuk']."</td>	
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['dosis']."</td>	
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>	
						  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jenisbibit']."</td>	
						</tr>";
		
		$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($resx);
		if($row<1)
		{
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
				$skdBrg="select  namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$barx['kodebarang']."'";//echo $skdBrg;
				$qkdBrg = $owlPDO->query($skdBrg) or die(print " Gagal: " . PDOException::getMessage());
				$qkdBrg->setFetchMode(PDO::FETCH_ASSOC);
				$rBrg=$qkdBrg->fetch();
				
				$sBibit="select jenisbibit  from ".$dbname.".setup_jenisbibit where jenisbibit='".$barx['jenisbibit']."'" ;
				$qBibit = $owlPDO->query($sBibit) or die(print " Gagal: " . PDOException::getMessage());
				$qBibit->setFetchMode(PDO::FETCH_ASSOC);				
				$rBibit=$qBibit->fetch();
				
				$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$barx['kodeorg']."'";
				$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
				$qOrg->setFetchMode(PDO::FETCH_ASSOC);				
				$rOrg=$qOrg->fetch();	
				$stream.="	<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$barx['periodepemupukan']."</td>
					<td>".$rOrg['namaorganisasi']."</td>
					<td>".$barx['blok']."</td>
					<td>".$barx['tahuntanam']."</td>
					<td>".$rBrg['namabarang']."</td>	
					<td>".$barx['dosis']."</td>	
					<td>".$barx['satuan']."</td>	
					<td>".$barx['jenisbibit']."</td>	
					</tr>";
			}
		}
	
	//echo "warning:".$strx;
//=================================================
		
	$stream.="</table>Print Time:".date('d-m-Y H:i:s')."<br>By:".$_SESSION['empl']['name'];	

$nop_="RekomendasiPupuk";
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
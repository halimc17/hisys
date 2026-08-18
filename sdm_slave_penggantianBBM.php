<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periode	=checkPostGet('periode','');
$kodeorg	=substr($_SESSION['empl']['lokasitugas'],0,4);
$karyawanid	=checkPostGet('karyawanid','');
$pt			=checkPostGet('pt','');   	
$notransaksi=checkPostGet('notransaksi','');
$keterangan	=checkPostGet('keterangan','');
$bytransport=checkPostGet('bytransport','');
$byperawatan=checkPostGet('byperawatan','');
$bytoll		=checkPostGet('bytoll','');
$bylain		=checkPostGet('bylain','');
$total		=checkPostGet('total','');
$method		=checkPostGet('method','');
$userid=$_SESSION['standard']['userid'];            
	
if($method=='delete')
{
	$str="delete from ".$dbname.".sdm_penggantiantransport where notransaksi='".$notransaksi."'";
}			
else if($method=='insert')
{
	$str="insert into ".$dbname.".sdm_penggantiantransport 
	      (`notransaksi`,`karyawanid`,`periode`,
		  `keterangan`,`toll`,`trans`,
		  `perawatan`,`kodeorg`,`alokasi`,
		  `updateby`,`bylain`,`totalklaim`)
		  values(
		   '".$notransaksi."',".$karyawanid.",'".$periode."',
		   '".$keterangan."',".$bytoll.",".$bytransport.",
		   ".$byperawatan.",'".$kodeorg."','".$pt."',
		   ".$userid.",".$bylain.", 0
		  )"; 
		  
		  //exit("error :".$str);
}
else if($method=='update')
{
	$str="update ".$dbname.".sdm_penggantiantransport
	      set 
		  `karyawanid`=".$karyawanid.",
		  `periode`='".$periode."',
		  `keterangan`='".$keterangan."',
		  `toll`=".$bytoll.",
		  `trans`=".$bytransport.",
		  `perawatan`=".$byperawatan.",
		  `kodeorg`='".$kodeorg."',
		  `alokasi`='".$pt."',
		  `updateby`=".$userid.",
		  `bylain`=".$bylain.",
		  `totalklaim`=0
		  where notransaksi='".$notransaksi."'"; 
		  //exit("error :".$str);
}
else
{
	$str="";
}

try{
	if($str!=''){
		$owlPDO->exec($str); 
	}
		
	if($periode==''){
		$periode=date('Y-m');
	}
	$str="select a.*,sum(b.jlhbbm) as bbm,c.namakaryawan from ".$dbname.".sdm_penggantiantransport a
		  left join ".$dbname.".sdm_penggantiantransportdt b 
		  on a.notransaksi=b.notransaksi
		  left join ".$dbname.".datakaryawan c
		  on a.karyawanid=c.karyawanid
		   where periode='".$periode."' and 
		  kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
		  group by notransaksi";
		  //exit($str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$res->fetch()){
		$no+=1;
		$add='';
		if($bar->posting==0){
			$add.=" <img src='images/application/application_edit.png' class=resicon onclick=\"editBBM('".$bar->notransaksi."','".substr($bar->periode,0,4)."-".substr($bar->periode,5,2)."','".$bar->alokasi."','".$bar->karyawanid."','".$bar->totalklaim."','".$bar->bbm."','".$bar->keterangan."')\" title='edit'>";
			$add.=" <img src='images/application/application_delete.png' class=resicon onclick=deleteBBM('".$bar->notransaksi."') title='delete'>";
		}
			$add.=" <img src='images/pdf.jpg' class=resicon onclick=previewBBM('".$bar->notransaksi."',event) title='view'>";
			echo"<tr class=rowcontent>
				 <td align=center>".$no."</td>
				 <td>".$bar->notransaksi."</td>
				 <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
				 <td>".$bar->alokasi."</td>
				 <td>".$bar->namakaryawan."</td>
				 <td align=right hidden>".number_format($bar->totalklaim,2,',','.')."</td>
				 <td align=right>".number_format($bar->dibayar,2,',','.')."</td>
				 <td>".tanggalnormal($bar->tanggalbayar)."</td>
				 <td align=right>".number_format($bar->bbm,2,',','.')."</td>
				 <td>".$bar->keterangan."</td>	
				 <td>".$add."</td>	 
			   </tr>";	
	}
}catch (PDOException $e){
	echo " Gagal ".addslashes($e->getMessage());
	die();
	
	//echo"dfasfasf";
}

?>
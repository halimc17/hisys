<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		


$method=checkPostGet('method','');
$kode=checkPostGet('kode','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$nilai=checkPostGet('nilai','');
$ket=checkPostGet('ket','');
$tipe=checkPostGet('tipe','');

$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
$tipesch=checkPostGet('tipesch','');




//exit("Error:$sInsert");	
$nmkode=makeOption($dbname,'pabrik_5mr_wtp','kode,nama');
$nmtipe=array('PA'=>'PARAMETER','VA'=>'VOLUME AIR');

if($tglsch=='--'){
    $tglsch='';
}

?>

<?php

switch($method){
	case 'insert':
		$str="INSERT INTO ".$dbname.".`pabrik_mr_bfwt` (`tipe`,`unit`,`tanggal`, `kode`, `nilai`,`updateby`,`keterangan`)
		values ('".$tipe."','".$_SESSION['empl']['lokasitugas']."','".$tgl."','".$kode."','".$nilai."','".$_SESSION['standard']['userid']."','".$ket."')";
		try{
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
        
	case 'update':
		$str="update ".$dbname.".pabrik_mr_bfwt set nilai='".$nilai."',updateby='".$_SESSION['standard']['userid']."',keterangan='".$ket."' 
		where kode='".$kode."' and tanggal='".$tgl."' and unit='".$_SESSION['empl']['lokasitugas']."' and tipe='".$tipe."' ";
		try{
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
	
	case'getkode':
		$optkode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tipe=='PA'){
			$str="SELECT * FROM ".$dbname.".pabrik_5mr_bfwt";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				if($kode==$bar['kode']){
					$select="selected=selected";
				} else {
					$select="";
				}
				$optkode.="<option ".$select." value=".$bar['kode'].">".$bar['nama']."</option>";
			}
		}
		echo $optkode;
	break;

	case'loaddata':
		echo"	<div id=container>
				<table class=sortable cellspacing=1 border=0>
				<thead>
					<tr class=rowheader>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>".$_SESSION['lang']['tipe']."</td>    
						<td align=center>".$_SESSION['lang']['kode']."</td>    
						<td align=center>".$_SESSION['lang']['nilai']."</td>   
						<td align=center>".$_SESSION['lang']['keterangan']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td></tr>
					</tr>
				</thead>
				<tbody>";
    
				$where="";
                if($tipesch!=''){
                    $where.=" and tipe='".$tipesch."' ";
                }
                if($tglsch!=''){
                    $where.=" and tanggal='".$tglsch."' ";
                }
                
                $limit=10;
				$page=0;
				if(isset($_POST['page'])){
					$page=$_POST['page'];
					if($page<0)
					$page=0;
				}
				$offset=$page*$limit;
				$maxdisplay=($page*$limit);
				$str="select count(*) as jmlhrow from ".$dbname.".pabrik_mr_bfwt where unit='".$_SESSION['empl']['lokasitugas']."'  
						".$where." ";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
                    $jlhbrs= $bar['jmlhrow'];
                $no=$maxdisplay;
				$str="select * from ".$dbname.".pabrik_mr_bfwt where unit='".$_SESSION['empl']['lokasitugas']."' 
					".$where."   order by tanggal desc limit ".$offset.",".$limit."";
				
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch()) {
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
                    echo "<td align=left>".$nmtipe[$bar['tipe']]."</td>";
                    echo "<td align=left>".$nmkode[$bar['kode']]."</td>";
                    echo "<td align=right>".number_format($bar['nilai'],2)."</td>";
                    echo "<td align=left>".$bar['keterangan']."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' 
							onclick=\"edit('".$bar['kode']."','".tanggalnormal($bar['tanggal'])."','".$bar['nilai']."','".$bar['keterangan']."','".$bar['tipe']."');\">
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' 
							onclick=\"del('".tanggalnormal($bar['tanggal'])."','".$bar['kode']."','".$bar['tipe']."');\">
                            </td>";
                    echo "</tr>";
				}
                echo"
				<tr class=rowheader><td colspan=11 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";
	break;        

	case 'delete':
		$str="delete from ".$dbname.".pabrik_mr_bfwt where unit='".$_SESSION['empl']['lokasitugas']."' and
			kode='".$kode."' and tanggal='".$tgl."' and tipe='".$tipe."'";
		try{
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
	
	
	default:
	break;
}
?>
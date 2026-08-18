<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$jenis=checkPostGet('jenis','');
$kode=checkPostGet('kode','');
$nama=checkPostGet('nama','');
$keterangan=checkPostGet('keterangan','');
$method=checkPostGet('method','');

// Searching
$kodesch=checkPostGet('kodesch','');
$namasch=checkPostGet('namasch','');

$nmjenis=array("KPL"=>$_SESSION['lang']['kapal'],"PNT"=>$_SESSION['lang']['ponton'],"TRK"=>'Truck');

switch($method){   

	case'getkode':
		
		$str="select count(*) as counter from ".$dbname.".pmn_5kapalponton where jenis = '".$jenis."' ";
		// exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$awal=$bar['counter']+1;
			$counter=addZero($awal,7);
			
			echo $jenis.$counter;
			
	
	break;
    
    case 'insert':
		$str="INSERT INTO ".$dbname.".`pmn_5kapalponton` 
				(`jenis`, `kode`, `nama`, `keterangan`,
				`createby`,`createtime`)
				values ('".$jenis."','".$kode."','".$nama."','".$keterangan."',
				'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;

    case 'update':
        $str="update ".$dbname.".pmn_5kapalponton set 
				nama='".$nama."',
				keterangan='".$keterangan."',
				updateby='".$_SESSION['standard']['userid']."'			
				where kode='".$kode."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $str="delete from ".$dbname.".pmn_5kapalponton where kode='".$kode."'";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
        

    case'loaddata':
		echo"<div id=container>
			<table class=sortable cellspacing=1 border=0 width=100%>
			 <thead>
				 <tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['jenis']."</td>
					<td align=center>".$_SESSION['lang']['kode']."</td> 
					<td align=center>".$_SESSION['lang']['nama']."</td> 
					<td align=center>".$_SESSION['lang']['keterangan']."</td> 
					<td align=center>".$_SESSION['lang']['updateby']."</td> 
					<td align=center>".$_SESSION['lang']['action']."</td></tr>
				 </tr>
				</thead>
				<tbody>";
		$where="";
			
		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);

		if($kodesch != "") {
			$where .= " and kode LIKE '%".$kodesch."%'";
		}

		if($namasch != "") {
			$where .= " and nama LIKE '%".$namasch."%' ";
		}

		$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_5kapalponton where 1=1 ".$where."";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){  
			$jlhbrs= $jsl->jmlhrow;
		}
		$no=$maxdisplay;
		$str="select * from ".$dbname.".pmn_5kapalponton where 1=1 ".$where." limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			echo "<tr class=rowcontent>";
			echo "<td align=center>".$no."</td>";
			echo "<td align=left>".$nmjenis[$bar['jenis']]."</td>";
			echo "<td align=left>".$bar['kode']."</td>";
			echo "<td align=left>".$bar['nama']."</td>";
			echo "<td align=left>".$bar['keterangan']."</td>";
			echo "<td align=left>".getNamaKaryawan($bar['updateby'])."</td>";
			echo "<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' 
					onclick=\"fillField('".$bar['jenis']."','".$bar['kode']."','".$bar['nama']."','".$bar['keterangan']."');\">
					<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kode']."');\">
					</td>";
			echo "</tr>";//
		}
		echo"
		<tr class=rowheader><td colspan=7 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>
		</table>";
    break;
default:
}
?>
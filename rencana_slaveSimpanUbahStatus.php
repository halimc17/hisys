<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$namalokasi =$_POST['namalokasi'];
$status	   =$_POST['status'];
$method        =$_POST['method'];
if($method=='update'){
	$str="update ".$dbname.".rencana_lahan set statuspengakuan='".$status."'
	       where nama='".$namalokasi."'";
	try{
		$owlPDO->exec($str);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
}

$str1="select *, case statuspengakuan when 0 then '".$_SESSION['lang']['proses']."'
	    when 1 then '".$_SESSION['lang']['redyforoperation']."'
		 when 2 then '".$_SESSION['lang']['fail']."' 		 
		 end as stats
       from ".$dbname.".rencana_lahan        
	   order by tanggalmulai desc";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar1=$res1->fetch())
{
	$no+=1;
	echo"<tr class=rowcontent>
	   <td>".$no."</td>
		<td>".$bar1->nama."</td>
		<td>".tanggalnormal($bar1->tanggalmulai)."</td>
		<td>".$bar1->peruntukanlahan."</td>
		<td>".$bar1->desa."</td>
		<td>".$bar1->kecamatan."</td>
		<td>".$bar1->kabupaten."</td>
		<td>".$bar1->provinsi."</td>
		<td>".$bar1->negara."</td>
		<td>".$bar1->kontak."</td>
		<td>".$bar1->stats."</td>
		";
}	 
?>

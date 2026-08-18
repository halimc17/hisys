<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');
$uname=$_POST['uname'];

$str=$owlPDO->query("select a.*,b.namakaryawan,b.lokasitugas from ".$dbname.".user a 
		  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.namauser like '%".$uname."%'");
$str->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($str);
if($numrows>0){
	echo"<table class=sortable cellspacing=1 border=0 onmousedown=sorttable.makeSortable(this)>
		 <thead>
			   <tr>
			   <td>Uname</td>
			   <td>UserId</td>
			   <td>Name</td>
			   <td>Location</td>
			   <td>Status<br>Active/NotActive</td>
			   <td>Delete</td>
			   </tr>
			 </thead>
			 <tbody>";
		while($bar=$str->fetch()){
		$opt='';
		if($bar->status==0){
				$opt.="<input type=checkbox id=".$bar->namauser." title='Click to activate' onclick=\"setActivate('".$bar->namauser."');\">";
		}else{
				$opt.="<input type=checkbox id=".$bar->namauser." checked  title='Click to deActivate' onclick=\"setActivate('".$bar->namauser."');\">";
		}
		echo" <tr class=rowcontent id='row".$bar->namauser."'>
			  <td class=firsttd>".$bar->namauser."</td>
				  <td>".$bar->karyawanid."</td>
				   <td>".$bar->namakaryawan."</td>
					<td>".$bar->lokasitugas."</td>   
				  <td align=center>".$opt."</td>
				  <td align=center>
		  <img class=iconclick src=images/delete1.png  height=14px title='Delete' onclick=delUser('".$bar->namauser."','".$bar->karyawanid."')> &nbsp
		  </td>
		 </tr>";
	  }
echo"</tbody>
	 </table>";
}else{
	echo "No data found..";
}
?>

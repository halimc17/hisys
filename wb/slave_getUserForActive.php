<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$uname=$_POST['uname'];

$str=$owlPDO->query("select * from ".$dbname.".user where uname like '%".$uname."%'");
$str->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($str);
if($numrows>0){
	echo"<table class=sortable cellspacing=1 cellpadding=3 border=0 onmousedown=sorttable.makeSortable(this)>
		<thead>
		<tr>
			<td>Uname</td>
			<td>Status<br>Active/NotActive</td>
			<td>Delete</td>
		</tr>
		</thead>
		<tbody>";
		while($bar=$str->fetch()){
		$opt='';
		if($bar->status==0){
				$opt.="<input type=checkbox id=".$bar->uname." title='Click to activate' onclick=\"setActivate('".$bar->uname."');\">";
		}else{
				$opt.="<input type=checkbox id=".$bar->uname." checked  title='Click to deActivate' onclick=\"setActivate('".$bar->uname."');\">";
		}
		echo" <tr class=rowcontent id='row".$bar->uname."'>
			  <td class=firsttd>".$bar->uname."</td>
				  <td align=center>".$opt."</td>
				  <td align=center>
		  <img class=iconclick src=images/delete1.png  height=14px title='Delete' onclick=delUser('".$bar->uname."')> &nbsp
		  </td>
		 </tr>";
	  }
echo"</tbody>
	 </table>";
}else{
	echo "No data found..";
}
?>

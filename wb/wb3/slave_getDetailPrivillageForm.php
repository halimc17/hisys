<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');

if(count($_POST)>0){$param=$_POST;}else{$param=$_GET;}
echo"<div>
     <fieldset style='color:#333399;'>
	 <legend>[Info] Pengturan hak akses secara detil:</legend>
	 Pengaturan ini menggunakan metode detil. Setiap Penggguna hanya dapat mengakses menu yang diberikan haknya. 
	 </fieldset>
	 <hr>
	 	 <font color=#F8800A>Klik nama pengguna untuk memberikan hak..!</font>
	 ";


$opt='<option>0</option>';
for($d=1;$d<25;$d++)
{
	$opt.="<option>".$d."</option>";
}

$str=$owlPDO->query("select * from ".$dbname.".user order by uname");
$str->setFetchMode(PDO::FETCH_OBJ);

echo "<table width=100% cellspacing=1 cellpadding=3 border=0 class=data>
      <thead>
	  <tr class=rowheader>
	  <td>No.</td>
	  <td>NamaPengguna</td>
		 <td>Status</td>
	  </tr>	  
	  </thead>
	  <tbody>
	  ";
	$no=0;
	while($bar=$str->fetch())
	{
	  $no+=1;
	  echo"<tr bgcolor=#DEDEDE class=standardrow onclick=\"setMapUserMenu(event,this,'".$bar->uname."','".$param['sumber']."')\" title='Click to Append menu to user ".$bar->uname."'>
	         <td align=right class=firsttd>".$no."</td>
                        <td>".$bar->uname."</td>";
	   if($bar->status==1)
	     echo"<td><font color=#00AA00><b>Active</b></td>"; 
	   else
	   	 echo"<td>Inactive</td>";   		 
	 echo "</tr>";
	}
echo"</tbody></table><br>";	  		
echo "
<input type=button value='Shahkan' class=mybutton onclick=window.location.reload()>
<input type=button value='Tutup' class=mybutton onclick=\"hideDetailForm('ctr','ctrmenu');hideThis('lab3');\">
<br><br>";
?>

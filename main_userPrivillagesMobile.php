<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/menusettingmobile.js></script>
<link rel=stylesheet type=text/css HREF=style/privillages.css>
<?
include('master_mainMenu.php');

//set max_id for menu============
$str=$owlPDO->query("select max(id) as id from ".$dbname.".menu");
$str->setFetchMode(PDO::FETCH_NUM);
$max_id=0;
while($bar=$str->fetch())
{
	$max_id=$bar[0];
}
echo"<script language=javascript1.2>
     max_id=".$max_id."
	 </script>";
//*************************************	 
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['privconf']).'</span>');
echo OPEN_THEME($_SESSION['lang']['privconf'].':');
//**********************************
//Main content
echo"<div>
	<fieldset style='width:450px;color:#333399;'>
		<legend>[Info] ".$_SESSION['lang']['userdetailprivsetup'].":</legend>
		".$_SESSION['lang']['thisusesdetailpriv']." 
	</fieldset>
	
	<input type=button value='".$_SESSION['lang']['apply']."' class=mybutton onclick=window.location.reload()>
	<hr>
	<font color=#F8800A>".$_SESSION['lang']['clickuser']."..!</font>";
	
$opt='<option>0</option>';
for($d=1;$d<25;$d++)
{
	$opt.="<option>".$d."</option>";
}

$str=$owlPDO->query("select a.*,b.namakaryawan,b.lokasitugas from ".$dbname.".user a left join
         ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid order by a.namauser");
$str->setFetchMode(PDO::FETCH_OBJ);

echo "<table width=100% cellspacing=1 border=0 class=data>
      <thead>
	  <tr class=rowheader>
	  <td>No.</td>
	  <td>".$_SESSION['lang']['username']."</td>
	      <td>UID</td>
	      <td>Status</td>
                         <td>Empl.Name</td>
                         <td>Location</td>
	  </tr>	  
	  </thead>
	  <tbody>
	  ";
	$no=0;
	while($bar=$str->fetch())
	{
	  $no+=1;
	  echo"<tr bgcolor=#DEDEDE class=standardrow onclick=\"setMapUserMenu(event,this,'".$bar->namauser."')\" title='Click to Append menu to user ".$bar->namauser."'>
	         <td align=right class=firsttd>".$no."</td>
                        <td>".$bar->namauser."</td>
                        <td>".$bar->karyawanid."</td>
                        <td>".$bar->namakaryawan."</td>
                        <td>".$bar->lokasitugas."</td>";
	   if($bar->status==1)
	     echo"<td><font color=#00AA00><b>Active</b></td>"; 
	   else
	   	 echo"<td>Inactive</td>";   		 
	 echo "</tr>";
	}
echo"</tbody></table><br>";	  		
echo "
<input type=button value='".$_SESSION['lang']['apply']."' class=mybutton onclick=window.location.reload()>
<br><br>";

echo CLOSE_THEME();

echo"<div id=ctr style='position:absolute;display:none;'>";
echo OPEN_THEME('Menu/User Level And Privileges:');
	echo"<div id=content>";
    echo"</div>";
echo CLOSE_THEME();
echo"</div>";

echo"<div id=ctrmenu style='position:absolute;display:none;'>";
echo OPEN_THEME('Menu Mapping:');
    echo"<div id=contentmenu>";
    echo"</div>";
echo CLOSE_THEME();
echo"</div>";

echo"<div id=globalakses style='position:absolute;display:none;'>";
echo OPEN_THEME('Choose Menu:');
	echo"<div id=contentglobal>";
    echo"</div>";
echo CLOSE_THEME();
echo"</div>";        


CLOSE_BOX();	
echo close_body();
?>

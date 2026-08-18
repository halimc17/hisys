<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_payrollHO.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?
include('master_mainMenu.php');

$arAkun2 = makeOption($dbname,"keu_5akun", "noakun,namaakun","NOT (substr(noakun, 1,1)=2 or substr(noakun, 1,1)=6 or substr(noakun, 1,1)=7 or substr(noakun, 1,1)=8)");
$arAkun = makeOption($dbname,"keu_5akun", "noakun,namaakun","NOT (substr(noakun, 1,1)=2 or substr(noakun, 1,1)=6 or substr(noakun, 1,1)=7 or substr(noakun, 1,1)=8)",2);
$optAkun = "<option value=''></option>";
foreach ($arAkun as $key => $val) {
	$optAkun.="<option value=".$key.">".$val."</option>";
}
$arryesno = array('0'=>"TIDAK", '1'=>'YA');
$arryesno2 = array('0'=>"TIDAK", '1'=>'YA', '3'=>'SEMI');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['komponenpayroll']).'</span>');
		echo"<div id=EList>";
		echo OPEN_THEME($_SESSION['lang']['input']);
		echo"<table><tr>";
		echo"
			<td valign=top width=350px>
			     <fieldset style='text-align:left'>
				   <legend id=legend><b>".$_SESSION['lang']['new']."</b></legend>
				   <table><tr><td>
				   <input type=hidden value='' id=compid>
				   ".$_SESSION['lang']['namakomponen']."</td><td><input style='width:197px;' class=myinputtext type=text id=comp size=30 onkeypress=\"return tanpa_kutip(event)\">
				   </td></tr>
				   <tr><td>
				   ".$_SESSION['lang']['status']."</td><td><select id=plus style='width:200px;' >
				     <option value=1>".$_SESSION['lang']['penambah']."</option>
					 <option value=0>".$_SESSION['lang']['pengurang']."</option>
				   </select>
				   </td></tr>
				   <tr><td>				   
				   ".$_SESSION['lang']['tipe']."</td><td><select id=type style='width:200px;' >
				     <option value=basic>Basic</option>
					 <option value=additional>Additional</option>
				   </select>
				   </td></tr>
				   <tr><td>	
				    ".$_SESSION['lang']['tipeinput']."</td><td><select id=lock style='width:200px;' >
				     <option value=0>Bebas Input</option>
					 <option value=1>Kunci Input</option>
				   </select>
				   </td></tr>
				   <tr hidden><td>	
				    ".$_SESSION['lang']['pph21']."</td><td><input type=checkbox id='pph21'>
				   </td></tr>
				   <tr hidden><td>	
				    Irregular</td><td><select id=irregular style='width:200px;' >
				     <option value=0>Tidak</option>
					 <option value=1>Ya</option>
					 <option value=3>Regular dengan perhitungan Irregular</option>
				   </select>
				   </td></tr>
					<tr>
						<td></td>
						<td>
						   <button class=mybutton onclick=saveComp()>".$_SESSION['lang']['save']."</button>	 
						   <button class=mybutton onclick=cancelComp()>".$_SESSION['lang']['cancel']."</button>	
						</td>
					</tr>
				   </table>
				 </fieldset>
			 </td>
		
			<td valign=top>
			     <fieldset style='text-align:left'>
				   <legend><b><img src=images/info.png align=left height=35px valign=asmiddle>[Info]</b></legend>
                   ".$_SESSION['lang']['komponendeskripsi']." 
				 </fieldset>
			</td>
			 </tr>
			 </table>
			 ";	 
		echo CLOSE_THEME();
		echo"</div>";
	CLOSE_BOX();
	OPEN_BOX();
		echo OPEN_THEME($_SESSION['lang']['list']);
		$str="select * from ".$dbname.".sdm_ho_component order by id";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		echo"<table class=sortable cellpadding=5 cellspacing=1 border=0>
		     <thead>
			   <tr class=rowheader><td align=center>No.</td><td align=center>".$_SESSION['lang']['nama']."</td>
			   <td align=center>Status</td><td align=center>".$_SESSION['lang']['tipe']."</td>
			   <td align=center>".$_SESSION['lang']['sumber']."</td>
			   <td hidden align=center>".$_SESSION['lang']['pph21']."</td>
			   <td hidden align=center>Irregular</td>
			   <td align=center colspan=2>Action</td></tr>
			 </thead>
			 <tbody id=tablebody>
			 ";
		while($bar=$res->fetch()){
			$no+=1;
			echo "<tr class=rowcontent style=height:25px><td class=fisttd align=center>".$no."</td>
			      <td>".$bar->name."</td>
			      <td>".($bar->plus==1?$_SESSION['lang']['penambah']:$_SESSION['lang']['pengurang'])."</td>
				  <td>".$bar->type."</td>
				  <td>".($bar->lock==1?$_SESSION['lang']['dikunci']:$_SESSION['lang']['inputbebas'])."</td>
			      <td hidden>".$arryesno[$bar->pph21]."</td>
			      <td hidden>".$arryesno2[$bar->irregular]."</td>
				  <td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn title=Edit height=11px onclick=\"editComp('".$bar->id."','".$bar->name."','".$bar->plus."','".$bar->type."','".$bar->lock."','".$bar->pph21."','".$bar->irregular."')\"></td> 
				  <td hidden align=center width=25px><img src=images/application/application_delete.png  height=11px class=zImgBtn title=Delete  onclick=\"delComp('".$bar->id."','".$bar->name."')\"></td>
				  </tr>";
		}
		echo"</tbody>
		     <tfoot>
			 </tfoot>
			 </table>";
		echo CLOSE_THEME();	 
	CLOSE_BOX();
echo close_body();
?>

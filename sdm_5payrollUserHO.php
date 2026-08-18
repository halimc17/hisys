<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src=js/sdm_payrollHO.js></script>
<link rel=stylesheet type=text/css href=style/payrollHO.css>
<?
//+++++++++++++++++++++++++++++++++++++++++++++
//list employee
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pengaturanuserpayroll']).'</span>');	
		echo"<div id=EList>";
		echo OPEN_THEME($_SESSION['lang']['daftarpengguna'].':');
		$str1="select namauser from ".$dbname.".user order by namauser";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_NUM);
		$opt='';
		while($bar1=$res1->fetch())
		{
			$opt.="<option value='".$bar1[0]."'>".$bar1[0]."</option>";
		}
	echo"<fieldset>
		         <legend>
				 <img src=images/info.png align=left height=35px valign=asmiddle>
				 [INFO]
				 </legend>
				 ".$_SESSION['lang']['assignpyinfo']."	
		      </fieldset>	
			  <fieldset>
			  New User:<select id=user><option></option>".$opt."</select> Type<select id=type><option value='operator'>Operator</option><option value='admin'>Admin</option></select>
		      <button onclick=savePyUser() class=mybutton>".$_SESSION['lang']['save']."</button>
			  </fieldset>";
		echo"<table class=sortable cellspacing=1 width=500px border=0>
		     <thead>
			   <tr class=rowheader><td>No.</td><td>".$_SESSION['lang']['username']."</td>
			   <td>".$_SESSION['lang']['tipe']."</td>
			   <td>Del</td>
			   </tr>
			 </thead>
			 <tbody id=tablebody>
			 ";
		$str="select * from ".$dbname.".sdm_ho_payroll_user order by uname";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch())
		{
			$no+=1;
			echo "<tr class=rowcontent><td class=fisttd>".$no."</td>
			      <td id='uname".$no."'>".$bar->uname."</td>
			      <td>".$bar->type."</td>
				  <td align=center><img src=images/close.png  height=11px class=dellicon title=Delete  onclick=\"delPyUser('".$bar->uname."')\"></td>	  
				  </tr>";
		}
		echo"</tbody>
		     <tfoot>
			 </tfoot>
			 </table>";	 
		echo CLOSE_THEME();
		echo"</div>";
	CLOSE_BOX();	
//+++++++++++++++++++++++++++++++++++++++++++
echo close_body();
?>
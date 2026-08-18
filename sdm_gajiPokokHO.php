<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_payrollHO.js></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?
include('master_mainMenu.php');
//+++++++++++++++++++++++++++++++++++++++++++++
	OPEN_BOX('','<b>BASIC SALARY SETUP:</b>');
		echo"<div id=EList>";
		echo OPEN_THEME('Basic Salary Form:');
//cek if new employee exist
		 $prestr="select distinct karyawanid from ".$dbname.".sdm_ho_employee order by karyawanid";
		 $preres = $owlPDO->query($prestr) or die(print " Gagal: " . PDOException::getMessage());
		 $preres->setFetchMode(PDO::FETCH_OBJ);
		 $arrid='';
		 while($prebar=$preres->fetch())
		 {
		 	if($arrid=='')
			 $arrid.=$prebar->karyawanid;
			else 
			 $arrid.=",".$prebar->karyawanid;			
		 }
         if($arrid=='')
		 {
		 	$arrid="'null'";
		 }

		 $str="select karyawanid,namakaryawan,statuspajak,tanggalkeluar,npwp from ".$dbname.".datakaryawan
		       where karyawanid not in(".$arrid.") and alokasi=1";
	     $preres = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		 $preres->setFetchMode(PDO::FETCH_OBJ);
		$newempl=owlBaris($preres);	 
		if($newempl>0)
		{
	       	echo "Warning!!!<br>
				      <img src=images/onebit_36.png height=50px align=middle>
					  Ada karyawan baru yang belum terdaftar di payroll, lakukan <b>Sinkronisasi</b> data jika perlu.";		
		}
//		else
//		{
			//ccheck if all employee has been assign to payroll operator
			$str="select count(*) as d from ".$dbname.".sdm_ho_employee
			      where operator is null or operator=''";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		    $res->setFetchMode(PDO::FETCH_OBJ);
			$count=0;
			while($bar=$res->fetch()){
				$count=$bar->d;
			}
	        if($count>0){
	        	echo "Forbidden!!!<br>
				      <img src=images/stop1.png height=100px align=middle>
					  Masih ada karyawan yang belum di <b>set operator</b> payroll-nya.";
	        }
			else
			{

					echo"<table><thead></thead>
					     <tbody><tr><td>";
					$stra="select id,name from ".$dbname.".sdm_ho_component where type='basic'
					       order by name";
					$resa = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		    		$resa->setFetchMode(PDO::FETCH_OBJ);					       
					$arrName=array();
					$arrIdx=array();
					while($bara=$resa->fetch())	   {
					  array_push($arrIdx,$bara->id);
					  array_push($arrName,$bara->name);	
					}
					
					$str="select karyawanid,name from ".$dbname.".sdm_ho_employee 
					      where operator='".$_SESSION['standard']['username']."'
						  order by name";  
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		    		$res->setFetchMode(PDO::FETCH_OBJ);
					echo"<table class=data celspacong=1 border=0>
					     <thead>
					     <tr class=rowheader align=center>
						     <td><b>No.</b></td>
							 <td><b>No.Karyawan</b></td>
						     <td><b>Nama.Karyawan</b></td>
							 <td><b>Basic Salary</b></td></tr>
						</thead>
						<tbody>";
						$n=0;
					while($bar=$res->fetch()){
						$n+=1;
						echo"<tr class=rowcontent>
						<td class=firsttd>".$n."</td>
						<td>".$bar->karyawanid."</td>
						<td>".$bar->name."</td>
						<td>";
						 echo"<table class=data celspacong=1 border=0>
						      <thead>
							  <tr class=rowheader align=center>
							  <td>Component</td><td>Value(Rp.)</td>
							  <td>**</td>
							  </tr>
							  </thead>";
						for($x=0;$x<count($arrName);$x++)
						{
							$strf="select `value` from ".$dbname.".sdm_ho_basicsalary 
							      where karyawanid=".$bar->karyawanid."
								  and component=".$arrIdx[$x];
							$val=0;	 
							$resf = $owlPDO->query($strf) or die(print " Gagal: " . PDOException::getMessage());
		    				$resf->setFetchMode(PDO::FETCH_OBJ); 
							while($barf=$resf->fetch())
							{
								$val=$barf[0];
							}
							echo"<tr class=rowcontent>
							     <td>".$arrName[$x]."</td>
								 <td>
								 	<input type=text class=myinputtextnumber value=".number_format($val,2,'.',',')." id=value".$n.$x." size=13 onkeypress=\"return angka_doang(event);\" onblur=\"change_number(this);\" maxlength=14>
								 </td>
								 <td><img src=images/save.png height=13px class=dellicon title='Save' onclick=saveBSalary('".$bar->karyawanid."','".$arrIdx[$x],"','value".$n.$x."')>
								 </td>
								 </tr>
								"; 
						}	  
						echo"<tfoot></tfoot></table>";	  
						echo"</td></tr>";
					}    
					
					echo"</tbody><tfoot></tfoot>
					</table>";
				echo"</td><td valign=top>";
				echo"<fieldset style='width:300px'>
 				 <legend>
				 <img src=images/info.png align=left height=35px valign=asmiddle>
				 </legend><p>
				 Setiap karyawan harus di set basic salary-nya. Basic salary ini akan otomatis menjadi default
				 pada saat pembuatan rekap gaji bulanan. Untuk menambahkan komponen lain sebagai basic salary, gunakan menu
				 <b>Payroll Component</b>, dan jadikan tipe komponen-nya menjadi <b>Penambah</b>.
				 </p><p>Basic Salary di-Update hanya pada saat adanya perubahan gaji(kenaikan/penurunan).
				     </p>
					 </fieldset>";
				echo"</td></tr></tbody><tfoot></tfoot></table>";	
//				}				
				
			}
//       }
		echo CLOSE_THEME();
		echo"</div>";
	CLOSE_BOX();	
//+++++++++++++++++++++++++++++++++++++++++++
echo close_body();
?>
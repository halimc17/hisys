<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

//+++++++++++++++++++++++++++++++++++++++++++++
//list employee
$periode=$_GET['periode'];
$tipe=$_GET['tipe'];
$username=$_GET['username'];
if($username=='')
{
	$username=$_SESSION['standard']['username'];
}
else
{
	$username=$username;
}
$str="select id,name from ".$dbname.".sdm_ho_component order by id";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$head="Payroll Periode: ".$periode.", Operator: ".$periode."
        <table border=1>
       <thead><tr>
	   	<td class=firsttd>No.</td>
		<td align=center>No.Karyawan</td>
		<td align=center>Nama.Karyawan</td>
		<td align=center>Dept.</td>
		<td align=center>Likasi.Tugas</td>
		<td align=center>Periode<br>Gaji</td>
		<td align=center>Bank</td>
		<td align=center>Bank.A/C</td>		
		<td align=center>Total T.H.P<br>(Rp.)</td>";
$arrUrut=array();		
$arrVal=array();
while($bar=$res->fetch())
{
	array_push($arrUrut,$bar->id);
	$head.="<td align=center>".str_replace(" ",".",$bar->name)."</td>";
}  
$head.="</tr></thead><tbody>";	

//get All user id from employee table
$str1="select distinct e.karyawanid,e.name,e.bank,e.bankaccount from ".$dbname.".sdm_ho_employee e,".$dbname.".sdm_ho_detailmonthly m
       where e.operator='".$username."'
	   and e.karyawanid=m.karyawanid and periode='".$periode."'
       order by e.name";	   
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_NUM);
$no=0;
$grandTotal=0;
while($bar1=$res1->fetch()){
	$no+=1;
	$total=0;	
   //get jabatan, departement, tmk
   $stg="select a.lokasitugas,a.bagian from ".$dbname.".datakaryawan a
         where karyawanid=".$bar1[0];	
   $reg=$owlPDO->query($stg) or die(print " Gagal: ".PDOException::getMessage());
   $reg->setFetchMode(PDO::FETCH_OBJ); 
	$emplloc='';
	$dept='';
	while($barg=$reg->fetch()){
		$dept=$barg->bagian;
		$emplloc=$barg->lokasitugas;
	}	 
	
	//makesure the value always zero on loop each employee
	for($z=0;$z<count($arrUrut);$z++)
	{
			$arrVal[$z]=0;
	} 
   //loop each employe to monthlypayment
	$str2="select component,value from ".$dbname.".sdm_ho_detailmonthly
		   where karyawanid=".$bar1[0]." and periode='".$periode."'
		   and `type`='".$tipe."' order by component";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
    $res2->setFetchMode(PDO::FETCH_OBJ); 
	while($bar2=$res2->fetch())
	{
		//fill arrVal base on component
		for($z=0;$z<count($arrUrut);$z++)
		{
			if($arrUrut[$z]==$bar2->component)
			{
				$arrVal[$z]=$bar2->value;
			}
		}
	 $total+=$bar2->value;	
	}
//assign to string
$head.="<tr>
        <td class=firsttd>".$no."</td>
		<td>'".$bar1[0]."</td>
		<td>".$bar1[1]."</td>
		<td>".$dept."</td>	
		<td>".$emplloc."</td>		
		<td align=center>".substr($periode,5,2)."-".substr($periode,0,4)."</td>
		<td>".$bar1[2]."</td>
		<td>'".$bar1[3]."</td>
		<td align=right><b>".number_format($total,2,'.','')."</b></td>";
 for($c=0;$c<count($arrVal);$c++)
 {
 	$head.="<td align=right>".number_format($arrVal[$c],2,'.','')."</td>";
 }		
$head.="</tr>";
//add terbilang below value row
/*
$terbilang='-';
$str3="select terbilang from ".$dbname1.".payrollterbilang
        where userid=".$bar1[0]." and `type`='".$tipe."'
		and periode='".$periode."' limit 1";	
{
	$terbilang=$bar3->terbilang;
}		
$head.="<tr><td bgcolor=#ffffff colspan='".(count($arrVal)+1)."'>".$terbilang."</td></tr>"; 
*/
$grandTotal+=$total; 		   
}	     
$head.="</tbody><tfoot>
        <tr><td colspan=8>Grand Total</td>
		<td align=right>".number_format($grandTotal,2,'.','')."</td>
		<td clspan=".count($arrVal)."></td>
		</tfoot></table>";
		
$stream=$head;		
$nop_="payroll_".$periode."_".$username;
if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != "index.html") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
 }
fclose($handle);
} 
?>
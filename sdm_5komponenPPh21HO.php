<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_payrollHO.js></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5komponenPPh21HO').'</span><br>');
echo"<div id=EList>";
echo OPEN_THEME('Component Gaji yang dikenai PPh21')."";

$str="select id,name,pph21 from ".$dbname.".sdm_ho_component
      where plus=1 order by id";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ); 

$va="Beri tanda check(V) pada komponen yang kena pajak.
     <table class=sortable cellpadding=5 cellspacing=1 border=0 width=500px>
      <thead>
	  <tr class=rowheader>
	    <td>ID.</td><td align=center>Nama.Komponen</td><td align=center width=50px>Yes / No</td>
	  </tr>	
	  </thead>
	  <tbody>";
while($bar=$res->fetch())
{
	if($bar->pph21==1){
		$ch='checked';
		if($bar->id==1){
			$ch.=" disabled";
		}
		if($bar->id==2)
		{
			$ch.=" disabled";
		}
		if($bar->id==99)
		{
			$ch.=" disabled";
		}
		if($bar->id==118)
		{
			$ch.=" disabled";
		}
		if($bar->id==101)
		{
			$ch.=" disabled";
		}
	}
	else
	    $ch='';
	$va.="<tr class=rowcontent>
	        <td class=firsttd align=center>".$bar->id."</td>
			<td>".$bar->name."</td>
			<td align=center><input type=checkbox id=ch".$bar->id." ".$ch." onclick=savePPh21Component(this,this.value) value=".$bar->id."></td>
	    </tr>"; 
}	  
$va.="</tbody><tfoot></tfoot></table>";	  	  

echo $va;	  			 
echo"</div>";
echo CLOSE_THEME();		
CLOSE_BOX();
	echo close_body();	
?>
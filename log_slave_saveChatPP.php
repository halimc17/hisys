<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid = $_SESSION['standard']['userid'];
$nopp = $_POST['nopp'];
$kodebarang	= $_POST['kodebarang']; 
   
if(isset($_POST['pesan'])){
	$pesan = $_POST['pesan'];
	$str= "insert into ".$dbname.".log_pp_chat (`nopp`,`karyawanid`,
          `pesan`,`kodebarang`)
		  values('".$nopp."',".$karyawanid.",'".$pesan."','".$kodebarang."')"; 	
	try{
		$owlPDO->exec($str);
		
		$str="select dibuat from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res=fetchdata($str);
		$dibuat=$res[0]['dibuat'];
		
		if($dibuat==$_SESSION['standard']['userid']){
			## CREATE NOTIFICATION
			$msgdt = "Adan pesan Chat terkait PR/SR dengan No ".$nopp." dari ".getNamaKaryawan($dibuat);
			$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where  bagian like '%PRO%' and tanggalkeluar='0000-00-00'";
			$res=fetchdata($str);
			foreach($res as $val){
				createnotif($nopp,'CHAT',$msgdt,$val['karyawanid'],date('Y-m-d H:i:s'));
			}
		}else{
			## CREATE NOTIFICATION
			$msgdt = "Ada pesan Chat terkait PR/SR dengan No ".$nopp." dari ".$_SESSION['empl']['name'];
			createnotif($nopp,'CHAT',$msgdt,$dibuat,date('Y-m-d H:i:s'));
		}
	}catch(PDOException $e){
		print " Gagal  !: " . $e->getMessage() . "\n"; 
		die(); 
	}
}

echo "<table class=sortable cellspacing=1 border=0 width=100%>
	<tr>
		<td>From</td>
		<td>Time</td>
		<td>Messages</td>
	</tr>";

$where="";
if($kodebarang!=''){
	$where= " and (a.kodebarang='".$kodebarang."' or a.kodebarang='')";
}

$str="select a.*,b.namauser from ".$dbname.".log_pp_chat a left join ".$dbname.".user b
	on a.karyawanid=b.karyawanid
	where 1=1 and a.nopp='".$nopp."' ".$where." order by a.kodebarang asc, tanggal asc";	 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

$no=0;
while($bar=$res->fetch())
{
	$no+=1;
	if($no%2==0)
	{
		$ct="style='background-color:#FFFFFF'";
	}
	else
	{
		$ct="style='background-color:#E8F2FE'";
	}
	echo"<tr>
		<td ".$ct.">".$bar->namauser."</td>
		<td ".$ct.">".$bar->tanggal."</td>
		<td ".$ct.">".$bar->pesan."</td>
	</tr>";
}
   
echo"</table>"; 
?>

<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/zLib.php');

$method          = checkPostGet('method','');

$kodeunit        = checkPostGet('kodeunit','');
$jenispersetujuan= checkPostGet('jenispersetujuan','');
$toleransi       = checkPostGet('toleransi','');
$status          = checkPostGet('status','');
$myid            = checkPostGet('myid','');
$digit            = checkPostGet('digit','');

$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");
$arrjns=array('3'=>'3 Digit / Per Kelompok Barang','5'=>'5 Digit / Per Sub Kelompok Barang','9'=>'9 Digit / Per Kode Barang');
switch($method){
	case 'loaddata':
		$tab="";
		$where = "1=1 ";
		if($kodeunit!=""){
			$where .= " and unit='".$kodeunit."'";
		}
		
		if($jenispersetujuan!=""){
			$where .= " and modul='".$jenispersetujuan."'";
		}
	
		$str="select * from ".$dbname.".setup_validasianggaran where ".$where." order by unit asc, modul asc, status desc";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['unit']."'");
				$optNmJns = makeOption($dbname,'setup_modulanggaran','kode,modul',"kode='".$val['modul']."'");				
				// $optNmKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
				//$optbagian= makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$val['departemen']."'");
				//$opttipe  = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$val['tipekaryawan']."'");
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:center'>".$val['unit']."</td>
					<td style='text-align:left'>".@$optNmOrg[$val['unit']]."</td>
					<td style='text-align:left'>".$val['modul']." - ".$optNmJns[$val['modul']]."</td>
					<td style='text-align:center'>".$arrjns[$val['digit']]."</td>
					<td style='text-align:center'>".$val['toleransi']."</td>
					<td style='text-align:center'>".$arrstatus[$val['status']]."</td>
					<td style='text-align:center'>
						<img src=images/skyblue/edit.png class=resicon caption='Edit' onclick=\"editfield('".$val['id']."','".$val['unit']."','".$val['modul']."','".$val['toleransi']."','".$val['status']."','".$val['digit']."');\"> 
					</td>
				</tr>";
			}
		}
		echo $tab;
	break;
	
	case'getkary':
		$where='';
		if($kodeunit!=''){
			$where.=" and kodeunit='".$kodeunit."'";
		}
		if($jenispersetujuan!=''){
			$where.=" and jenispersetujuan='".$jenispersetujuan."'";
		}
	
		$whr='';
		if($departemen!=''){
			$whr.=" and bagian='".$departemen."'";
		}
		if($jabatan!=''){
			$whr.=" and kodejabatan='".$jabatan."'";
		}

		if($tipekaryawan!=''){
			if($tipekaryawan==0){
				$whr.=" and tipekaryawan=9";
			} else if($tipekaryawan==9){
				$whr.=" and tipekaryawan=10";
			} else if($tipekaryawan==10){
				$whr.=" and tipekaryawan in (7,8)";
			}else{
				$whr.=" and tipekaryawan not in ('1','4','5','6')";
			}
		}else{
			$whr.=" and tipekaryawan not in ('1','4','5','6')";
		}

		$str = "select karyawanid,namakaryawan, lokasitugas from  " . $dbname . ".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and karyawanid not in (select karyawanid from  " . $dbname . ".setup_approval where 1=1 ".$where.") ".$whr." order by namakaryawan asc ";
		// exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optkar.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$optkar.="<option value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . " - ".$bar['lokasitugas']."</option>";
		}
		
	echo $optkar;
	break;
	
	case 'update':
		$str="update ".$dbname.".setup_validasianggaran set toleransi='".$toleransi."', status='".$status."', updateby='".$_SESSION['standard']['userid']."' ,updatetime='".date('Y-m-d H:i')."', digit='".$digit."' where id='".$myid."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'insert':
		$str="select * from ".$dbname.".setup_validasianggaran where unit='".$kodeunit."' and modul='".$jenispersetujuan."'";
		$res=fetchData($str);
		if(!empty($res)){
			exit("Warning : Unit dan Modul sudah pernah terdaftar disistem");
		}else{
			$str="insert into ".$dbname.".setup_validasianggaran (id,unit,modul,toleransi,status,createby,createtime,updateby,updatetime,digit) values ('','".$kodeunit."','".$jenispersetujuan."','".$toleransi."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$digit."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}	
	break;

	default:
	break;					
}

?>

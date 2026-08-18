<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');
$uname=checkPostGet('uname','');
$method=checkPostGet('method','');
$user=checkPostGet('user','');
$newempl=checkPostGet('newempl','');

switch($method){
	case 'finduser':
	$opt="<option value=''></option>";
	$str="select * from ".$dbname.".datakaryawan where tanggalkeluar='0000-00-00' order by namakaryawan asc ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$opt.="<option value=".$bar['karyawanid'].">".$bar['nik']." - ".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";
	}
			
	$str=$owlPDO->query("select a.*,b.namakaryawan,b.lokasitugas from ".$dbname.".user a 
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.namauser like '%".$uname."%'");
	$str->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($str);
	if($numrows>0){
		echo"<table class=sortable cellspacing=1 border=0 onmousedown=sorttable.makeSortable(this)>
			 <thead>
				   <tr>
				   <td>User Name</td>
				   <td>User Id</td>
				   <td>Employee Name</td>
				   <td>Location</td>
				   <td>Change Employee</td>
				   <td>Save</td>
				   </tr>
				 </thead>
				 <tbody>";
			while($bar=$str->fetch()){
			echo" <tr class=rowcontent id='row".$bar->namauser."'>
					<td class=firsttd id='uname".$bar->namauser."'>".$bar->namauser."</td>
					<td>".$bar->karyawanid."</td>
					<td>".$bar->namakaryawan."</td>
					<td>".$bar->lokasitugas."</td>   
					<td><select id=newempl".$bar->namauser.">" . $opt . "</select>
						<img id='newempl".$bar->namauser."' onclick=z.elSearch('newempl".$bar->namauser."',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;&nbsp;
						</td>
					<td align=center>
			  <img class=iconclick src=images/save.png  height=14px title='Delete' onclick=save('".$bar->namauser."')>
			  </td>
			 </tr>";
		  }
	echo"</tbody>
		 </table>";
	}else{
		echo "No data found..";
	}
	break;
	case'changeempl':
		if($newempl=='' or $uname==''){
			exit("Error : Silahkan pilih nama karyawan baru !!");
		}
		$str=" update user set karyawanid='".$newempl."' where namauser='".$uname."'";
		try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;
}
?>

<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$name = checkPostGet('name','');
$add = checkPostGet('add','');
$city = checkPostGet('city','');
$phone = checkPostGet('phone','');
$mail = checkPostGet('mail','');
$status = checkPostGet('status','');
$id = checkPostGet('id','');
$del = checkPostGet('del','');
$update = checkPostGet('update','');

if ($name!='' and $del=='' and $update=='') {
    $str = "insert into " . $dbname . ".sdm_5rs(
	  namars,alamat,telp,kota,email,status)
	  values(
		'" . $name . "','" . $add . "','" . $phone . "',
		'" . $city . "','" . $mail . "'," . $status . "
	  )";
} else if ($update!='') {
    $str = "update " . $dbname . ".sdm_5rs
	      set namars='" . $name . "',
		  alamat='" . $add . "',
		  email='" . $mail . "',
		  telp='" . $phone . "',
		  kota='" . $city . "',
		  status=" . $status . "
		  where id=" . $id;
} else if ($del!='') {
    $str = "delete from " . $dbname . ".sdm_5rs where
	  id =" . $id;
} else {
    $str = "select 1=1";
}
try{
	$owlPDO->exec($str); 
	
	$std = "select *, case status when 1 then 'Active' when 0 then 'Black List' end as xstatus
		  from " . $dbname . ".sdm_5rs order by namars";
	$res=$owlPDO->query($std) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $no = 0;
    while ($bad = $res->fetch()) {
        $no+=1;
        echo"<tr class=rowcontent>
			  <td class=firsttd>" . $no . "</td>
			  <td>" . $bad->namars . "</td>
			  <td>" . $bad->alamat . "</td>
			  <td>" . $bad->kota . "</td>
			  <td>" . $bad->telp . "</td>
			  <td>" . $bad->email . "</td>
			  <td>" . $bad->xstatus . "</td>
		      <td align=center>
			     <img src=images/tool.png class=dellicon title=Edit height=11px onclick=\"editHospital('" . $bad->id . "','" . $bad->namars . "','" . $bad->kota . "','" . $bad->alamat . "','" . $bad->telp . "','" . $bad->email . "','" . $bad->status . "')\">
		         <img src=images/close.png class=dellicon title=delete height=11px onclick=\"deleteHospital('" . $bad->id . "');\">
			  </td>
			</tr>";
    }
}
catch (PDOException $e){
	echo " Gagal," . addslashes($e->getMessage());
	die();
}
?>

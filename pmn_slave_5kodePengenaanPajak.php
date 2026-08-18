<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$kode=$_POST['kode'];
$nama=$_POST['nama'];

switch($proses){
case 'insert':
    $rrr='';

    if($kode=='')$rrr.=" Kode, ";
    if($nama=='')$rrr.=" Nama,";
    if($rrr!=''){
        echo "error: Silakan mengisi ".$rrr.".";
        exit;
    }  
    $s_cek="select * from ".$dbname.".pmn_5fakturkode where kode='".$kode."'";
    $q_cek=$owlPDO->query($s_cek) or die(print " Gagal: ".PDOException::getMessage());
    $cek=owlBaris($q_cek);
	$q_cek->setFetchMode(PDO::FETCH_ASSOC);

    if($cek<1){
        $simpan="INSERT INTO ".$dbname.".pmn_5fakturkode(kode,nama,createby,createtime)VALUES ('".$kode."','".$nama."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
        try{
			$owlPDO->exec($simpan);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
    }
    else{
        $update="UPDATE ".$dbname.".pmn_5fakturkode SET nama='$nama',updateby='" . $_SESSION['standard']['userid'] . "' WHERE kode='".$kode."'";
		try{
			$owlPDO->exec($update);
			echo 'Done.';
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
    }
break;
case 'loaddata':
    $limit=10;
    $page=0;
    if(isset($_POST['page']))
    {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
    }
    $sCount="select count(*) as jmlhrow from ".$dbname.".pmn_5fakturkode order by kode asc";
	$qCount=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
	$qCount->setFetchMode(PDO::FETCH_OBJ);
    while($rCount=$qCount->fetch()){
        $jmlbrs= $rCount->jmlhrow;
    }
    $offset=$page*$limit;
    if($jmlbrs<($offset))$page-=1;
    $offset=$page*$limit;
    $no=$offset;

    $sShow="select * from ".$dbname.".pmn_5fakturkode order by kode asc limit ".$offset.",".$limit." ";
    $qShow=$owlPDO->query($sShow) or die(print " Gagal: ".PDOException::getMessage());
	$qShow->setFetchMode(PDO::FETCH_ASSOC);
	while($row=$qShow->fetch())
    {
        $no+=1;
        $kode=$row['kode'];
        if(strlen($kode)<3){
            $kd="0".$kode;
        }
        else{
            $kd=$kode;
        }    
        echo"<tr class=rowcontent>
        <td id='no' align=center>".$no."</td>
        <td id='kode_".$no."' value='".$row['kode']."' align='center'>".$kd."</td>
        <td id='nama_".$no."' value='".$row['nama']."'>".$row['nama']."</td>
        <td>".getNamaKaryawan($row['updateby'])."</td>
        <td>
        <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editRow('".$row['kode']."','".$row['nama']."');\" >
        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$row['kode']."','".$row['nama']."');\" ></td>";
    }
    echo"
    </tr><tr class=rowheader><td colspan=6 align=center>
    ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jmlbrs."<br />
    <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
    <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
    </td>
    </tr>";
break;
case 'deletedata':
    $where="nama = '".$nama."'";
    $sDel="delete from ".$dbname.".pmn_5fakturkode where ".$where." and kode = '".$kode."'";
	try{
		$owlPDO->exec($sDel);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
break;

default:
break;	
}
?>
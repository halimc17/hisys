<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$afd=checkPostGet('afd','');
$asisten=checkPostGet('asisten','');
if(isset($_POST['tanggalpanen'])) {
	$tanggalpanen=substr(($_POST['tanggalpanen']),6,4)."-".substr(($_POST['tanggalpanen']),3,2)."-".substr(($_POST['tanggalpanen']),0,2);
} else {
	$tanggalpanen='';
}
$pemanen=checkPostGet('pemanen','');
$nopemanen=checkPostGet('nopemanen','');
$tph=checkPostGet('tph','');
$jjg_ast=checkPostGet('jjg_ast','');
$jjg_pnn=checkPostGet('jjg_pnn','');

$cari_ast=checkPostGet('cariasisten','');
$cariafd=checkPostGet('cariafd','');
if(isset($_POST['caritgl'])) {
	$cari_tgl=substr(($_POST['caritgl']),6,4)."-".substr(($_POST['caritgl']),3,2)."-".substr(($_POST['caritgl']),0,2);
} else {
	$cari_tgl='';
    
}
$old_afd=checkPostGet('old_afd','');
$old_ast=checkPostGet('old_ast','');
$oldpemanen=checkPostGet('oldpemanen','');
$old_tph=checkPostGet('old_tph','');

switch($method)
{
    case'insert':
    if($afd=='')
    {
        echo"warning: Silakan pilih afdeling"; exit();
    }
    if($asisten=='')
    {
        echo"warning: Silakan pilih nama asisten"; exit();
    }
    if($tanggalpanen=='')
    {
        echo"warning: Silakan isi tanggal panen"; exit();
    }
    if($pemanen=='')
    {
        echo"warning: Silakan pilih nama pemanen"; exit();
    }    
    if($nopemanen=='')
    {
        echo"warning: Silakan isi no pemanen"; exit();
    }
    if($tph=='')
    {
        echo"warning: Silakan isi no TPH"; exit();
    }
    if($jjg_ast=='')
    {
        echo"warning: Silakan isi jumlah JJG Asisten"; exit();
    }
    if($jjg_pnn=='')
    {
        echo"warning: Silakan isi jumlah JJG Kerani Panen"; exit();
    }
    $scek="select * from ".$dbname.".kebun_periksabuah where kodeorganisasi='".$kebun."' and tph='".$tph."'";
    $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
    $qcek->setFetchMode(PDO::FETCH_ASSOC);
    $rcek=owlBaris($qcek);
    if($rcek!=0){
       exit("error: Data sudah pernah diinput.");
    }
    else{
        $sIns="insert into ".$dbname.".kebun_periksabuah (nopemanen,kodeorganisasi,afdeling,asisten_id,pemanen_id,tgl_panen,tph,jjg_asisten,jjg_pemanen) 
               values ('".$nopemanen."','".$kebun."','".$afd."','".$asisten."','".$pemanen."','".$tanggalpanen."','".$tph."','".$jjg_ast."','".$jjg_pnn."')";
//        exit("error: ".$sIns);
        try{
            $owlPDO->exec($sIns); 
        }catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
            die();
        }
    }
    break;
    case'loadData':
    $no=0;	 
    $str="select *,b.namakaryawan as pemanen,a.pemanen_id as pemanenid,a.kodeorganisasi as kebun,a.afdeling as afd,a.nopemanen as nopnn,
          a.tph as tph, a.jjg_asisten as jjg_ast,a.jjg_pemanen as jjg_pnn, a.asisten_id as asisten, a.tgl_panen as tglpnn
          from ".$dbname.".kebun_periksabuah a left join ".$dbname.".datakaryawan b
          on a.pemanen_id=b.karyawanid where a.kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."'";
//    exit("error: ".$str);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $no+=1;	
        echo "<tr class=rowcontent>
	   <td rowspan=2 align=center>".$no."</td>
	   <td rowspan=2 align=left>".$bar['pemanen']."</td>
           <td rowspan=2 align=center>".$bar['kebun']."</td>
           <td rowspan=2 align=center>".$bar['afd']."</td>
		   <td rowspan=2 align=center>".tanggalnormal($bar['tglpnn'])."</td>
	   <td rowspan=2 align=center>".$bar['nopnn']."</td>
	  </tr>
          <tr class=rowcontent>
            <td align=right>".$bar['tph']."</td>
            <td align=right>".$bar['jjg_ast']."</td>
            <td align=right>".$bar['tph']."</td>
            <td align=right>".$bar['jjg_pnn']."</td>
            <td>
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kebun']."','".$bar['afd']."','".$bar['asisten']."','".tanggalnormal($bar['tglpnn'])."','".$bar['pemanenid']."','".$bar['nopnn']."','".$bar['tph']."','".$bar['jjg_ast']."','".$bar['jjg_pnn']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['kebun']."','".$bar['afd']."','".$bar['asisten']."','".$bar['tglpnn']."','".$bar['pemanenid']."','".$bar['nopnn']."','".$bar['tph']."','".$bar['jjg_ast']."','".$bar['jjg_pnn']."');\">             
            </td>
          </tr>";
        
    }  
    if($no==0){
        echo"<tr class=rowcontent>
        <td colspan=10>Data Empty.</td>
        </tr>";	
    }
    break;
    case'update':
    
    if($afd=='')
    {
        echo"warning: Silakan pilih afdeling"; exit();
    }
    if($asisten=='')
    {
        echo"warning: Silakan pilih nama asisten"; exit();
    }
    if($tanggalpanen=='')
    {
        echo"warning: Silakan isi tanggal panen"; exit();
    }
    if($pemanen=='')
    {
        echo"warning: Silakan pilih nama pemanen"; exit();
    }    
    if($nopemanen=='')
    {
        echo"warning: Silakan isi no pemanen"; exit();
    }
    if($tph=='')
    {
        echo"warning: Silakan isi no TPH"; exit();
    }
    if($jjg_ast=='')
    {
        echo"warning: Silakan isi jumlah JJG Asisten"; exit();
    }
    if($jjg_pnn=='')
    {
        echo"warning: Silakan isi jumlah JJG Kerani Panen"; exit();
    }
    
        $sUpd="update ".$dbname.".kebun_periksabuah set afdeling='".$afd."',asisten_id = '".$asisten."',tgl_panen='".$tanggalpanen."',
               pemanen_id='".$pemanen."',nopemanen='".$nopemanen."',tph='".$tph."', jjg_asisten = '".$jjg_ast."', jjg_pemanen='".$jjg_pnn."' 
               where kodeorganisasi='".$kebun."' and afdeling='".$old_afd."' and asisten_id = '".$old_ast."' and pemanen_id='".$oldpemanen."' 
               and tph='".$old_tph."'";
    //    exit("error: ".$sUpd);
        try{
            $owlPDO->exec($sUpd); 
        }catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
            die();
        }

    break;
    case 'deletedata':
    $tanggalpanen=checkPostGet('tanggalpanen','');
    $sDel="delete from ".$dbname.".kebun_periksabuah 
           where kodeorganisasi='".$kebun."' and afdeling='".$afd."' and tgl_panen='".$tanggalpanen."' 
           and asisten_id = '".$asisten."' and pemanen_id='".$pemanen."' and tph='".$tph."'";
//    exit("error: ".$sDel);
        try{
            $owlPDO->exec($sDel); 
        }catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
            die();
        }                      
    break;
    case'caritph':
    $no=0;    
    if($cariafd!=''&&$cari_ast!=''&&$cari_tgl!=''){
        $whr="(a.afdeling like '".$cariafd."' and a.tgl_panen like '".$cari_tgl."' and a.asisten_id like '".$cari_ast."')";
    }
    else
        $whr="(a.afdeling like '".$cariafd."' or a.tgl_panen like '".$cari_tgl."' or a.asisten_id like '".$cari_ast."')";
    
    $str="select *,b.namakaryawan as pemanen,a.pemanen_id as pemanenid,a.kodeorganisasi as kebun,a.afdeling as afd,a.nopemanen as nopnn,
          a.tph as tph, a.jjg_asisten as jjg_ast,a.jjg_pemanen as jjg_pnn, a.asisten_id as asisten, a.tgl_panen as tglpnn
          from ".$dbname.".kebun_periksabuah a left join ".$dbname.".datakaryawan b
          on a.pemanen_id=b.karyawanid where ".$whr." and a.kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."'";
//    exit("error: ".$whr);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $no+=1;	
        echo "<tr class=rowcontent>
	   <td rowspan=2 align=center>".$no."</td>
	   <td rowspan=2 align=left>".$bar['pemanen']."</td>
           <td rowspan=2 align=center>".$bar['kebun']."</td>
           <td rowspan=2 align=center>".$bar['afd']."</td>
		   <td rowspan=2 align=center>".tanggalnormal($bar['tglpnn'])."</td>
	   <td rowspan=2 align=center>".$bar['nopnn']."</td>
	  </tr>
          <tr class=rowcontent>
            <td align=right>".$bar['tph']."</td>
            <td align=right>".$bar['jjg_ast']."</td>
            <td align=right>".$bar['tph']."</td>
            <td align=right>".$bar['jjg_pnn']."</td>
            <td>
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kebun']."','".$bar['afd']."','".$bar['asisten']."','".tanggalnormal($bar['tglpnn'])."','".$bar['pemanenid']."','".$bar['nopnn']."','".$bar['tph']."','".$bar['jjg_ast']."','".$bar['jjg_pnn']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['kebun']."','".$bar['afd']."','".$bar['asisten']."','".$bar['tglpnn']."','".$bar['pemanenid']."','".$bar['nopnn']."','".$bar['tph']."','".$bar['jjg_ast']."','".$bar['jjg_pnn']."');\">             
            </td>
          </tr>";
        
    }  
    if($no==0){
        echo"<tr class=rowcontent>
        <td colspan=10>Data Empty.</td>
        </tr>";	
    }
    break;
    default:
    break;
}
?>
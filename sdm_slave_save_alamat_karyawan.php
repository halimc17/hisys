<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$alamatalamat=checkPostGet('alamatalamat',''); 
$alamatkota=checkPostGet('alamatkota',''); 
$alamatkodepos=checkPostGet('alamatkodepos',''); 
$alamattelepon=checkPostGet('alamattelepon',''); 
$alamatemplasement=checkPostGet('alamatemplasement',''); 
$alamatstatus=checkPostGet('alamatstatus',0); 
$alamatprovinsi=checkPostGet('alamatprovinsi',''); 			
$karyawanid=checkPostGet('karyawanid','');
$nourut=checkPostGet('nomor','');

if($alamatalamat!='' or (isset($_POST['del']) and $_POST['del']=='true') or isset($_POST['queryonly']))
{
        if($nourut=='')
           $nourut=0;

        if(isset($_POST['del']) and $_POST['del']=='true')
        {
                $str="delete from ".$dbname.".sdm_karyawanalamat where nomor=".$nourut;
        }
        else if(isset($_POST['queryonly']))
        {
                $str="";
        }
        else
        {
                if ($_POST['method'] == 'insert') {
                        $str="insert into ".$dbname.".sdm_karyawanalamat
                        (`karyawanid`,
                                        `alamat`,
                                        `kota`,
                                        `kodepos`,
                                        `telepon`,
                                        `emplasemen`,
                                        `aktif`,
                                        `provinsi`
                                )
                                values(".$karyawanid.",
                                '".$alamatalamat."',
                                '".$alamatkota."',
                                '".$alamatkodepos."',
                                '".$alamattelepon."',
                                '".$alamatemplasement."',
                                ".$alamatstatus.",
                                '".$alamatprovinsi."'
                                )";
                        // echo $str;
                        // exit();
                }else{
                        ##query update
                        $str="update ".$dbname.".sdm_karyawanalamat set karyawanid='".$karyawanid."',alamat='".$alamatalamat."', kota='".$alamatkota."', kodepos='".$alamatkodepos."', telepon='".$alamattelepon."',emplasemen='".$alamatemplasement."', aktif='".$alamatstatus."', provinsi='".$alamatprovinsi."' where nomor='".$nourut."'";
                        // echo $str;
                        // exit();
                
                }
        }
 if($str!=''){
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
 }
                 //jika alamat adalah aktif, update table datakaryawan
                 if($alamatstatus==1)
                 {
                        $strx="update ".$dbname.".datakaryawan set alamataktif='".$alamatalamat."',
                        kota='".$alamatkota."', provinsi='".$alamatprovinsi."'
                        where karyawanid=".$karyawanid;
                        $owlPDO->exec($strx);
                 }
                 $str="select *,case aktif when 1 then 'Yes' when 0 then 'No' end as status from ".$dbname.".sdm_karyawanalamat where karyawanid=".$karyawanid." order by nomor desc";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                 $no=0;
                 while($bar=$res->fetch())
                 {
                 $no+=1;	
                 echo"<tr class=rowcontent>
                                  <td class=firsttd align=center>".$no."</td>
                                  <td>".$bar->alamat."</td>			  
                                  <td>".$bar->kota."</td>
                                  <td>".$bar->provinsi."</td>			  
                                  <td>".$bar->kodepos."</td>			  
                                  <td>".$bar->emplasemen."</td>
                                  <td>".$bar->status."</td>
                                  <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delAlamat('".$karyawanid."','".$bar->nomor."');\"></td>
                                  <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editAlamat('".$bar->alamat."','".$bar->kota."','".$bar->kodepos."','".$bar->provinsi."','".$bar->telepon."','".$bar->emplasemen."','".$bar->aktif."','".$bar->nomor."');\"></td>
                                </tr>";	 	
                                $alamatalamat=checkPostGet('alamatalamat',''); 

                 }
}
else
{
        echo" Error: Incorrect Period";
}
?>
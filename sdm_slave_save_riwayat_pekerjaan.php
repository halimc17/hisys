<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$namaperusahaan=checkPostGet('namaperusahaan','');
$bidangusaha=checkPostGet('bidangusaha','');

$blnmasuk=checkPostGet('blnmasuk','');
$thnmasuk=checkPostGet('thnmasuk','');
$blnkeluar=checkPostGet('blnkeluar','');
$thnkeluar=checkPostGet('thnkeluar','');
//hitung masa kerja
$thn=intval($thnkeluar)-intval($thnmasuk);
$bln=intval($blnkeluar)-intval($blnmasuk);
$masakerja=(($thn*12)+$bln)/12;

//exit("Error:".$thn.":".$bln.":".$masakerja);
// exit("Error:".$namaperusahaan);
$blnkeluar=$blnkeluar."-".$thnkeluar;
$blnmasuk=$blnmasuk."-".$thnmasuk;
$jabatan=checkPostGet('jabatan','');
$bagian=checkPostGet('bagian','');
$alamat=checkPostGet('alamat','');
$karyawanid=checkPostGet('karyawanid','');
$nourut=checkPostGet('nomor','');

$gajipokok=checkPostGet('gajipokok','');
$alasanberhenti=checkPostGet('alasanberhenti','');
$tunjangan=checkPostGet('tunjangan','');
$lokasicuti=checkPostGet('lokasicuti','');
$nomor=checkPostGet('nomor','');

if($masakerja>0 or (isset($_POST['del']) and $_POST['del']=='true') or isset($_POST['queryonly']))
{
if($nourut=='')
   $nourut=0;
        if(isset($_POST['del']) and $_POST['del']=='true')
        {
                $str="delete from ".$dbname.".sdm_karyawancv where nomor=".$nourut;
        }
        else if( isset($_POST['queryonly']))
        {
                $str="";
        }
        else
        {
                if ($_POST['method'] == 'insert') {
                        $str="insert into ".$dbname.".sdm_karyawancv
                        (`karyawanid`,`namaperusahaan`,`bidangusaha`,`bulanmasuk`,`bulankeluar`,`jabatan`,`bagian`,
                                                `masakerja`,`alamatperusahaan`,`gajipokok`,`alasanberhenti`,`tunjangan`,`lokasicuti`)
                                                values(".
                                                $karyawanid.",'".$namaperusahaan."','".$bidangusaha."','".$blnmasuk."','".$blnkeluar."','".$jabatan."',
                                                '".$bagian."',".$masakerja.",'".$alamat."','".$gajipokok."','".$alasanberhenti."','".$tunjangan."','".$lokasicuti."')";
                }else{
                        ##query update
                        $str="update ".$dbname.".sdm_karyawancv set karyawanid='".$karyawanid."', namaperusahaan='".$namaperusahaan."', bidangusaha='".$bidangusaha."', bulanmasuk='".$blnmasuk."', bulankeluar='".$blnkeluar."',jabatan='".$jabatan."', bagian='".$bagian."', masakerja='".$masakerja."',alamatperusahaan='".$alamat."',gajipokok='".$gajipokok."',alasanberhenti='".$alasanberhenti."' where nomor='".$nomor."'";
                        // echo $str;
                        // exit('error');	

                        
                }
        }
 if($str!=''){
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
 }
$str="select *,right(bulanmasuk,4) as masup,left(bulanmasuk,2) as busup from ".$dbname.".sdm_karyawancv where karyawanid=".$karyawanid." order by masup,busup";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$mskerja=0;
while($bar=$res->fetch())
{
       $no+=1;	
        $msk=mktime(0,0,0,substr(str_replace("-","",$bar->bulanmasuk),0,2),1,substr($bar->bulanmasuk,3,4));	
        $klr=mktime(0,0,0,substr(str_replace("-","",$bar->bulankeluar),0,2),1,substr($bar->bulankeluar,3,4));	
        $dateDiff = $klr - $msk;
    $mskerja = floor($dateDiff/(60*60*24))/365; 

echo"	  <tr class=rowcontent>
                <td class=firsttd align=center>".$no."</td>
                <td>".$bar->namaperusahaan."</td>
                <td>".$bar->bidangusaha."</td>
                <td>".$bar->bulanmasuk."</td>
                <td>".$bar->bulankeluar."</td>
                <td>".$bar->jabatan."</td>
                <td>".$bar->bagian."</td>
                <td>".number_format($mskerja,2,',','.')." Th.</td>
                <td>".$bar->alamatperusahaan."</td>	
                <td>".$bar->gajipokok."</td>	
                <td>".$bar->alasanberhenti."</td>
                <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPengalaman('".$karyawanid."','".$bar->nomor."');\"></td>
                <td align=center><img src=images/application/application_edit.png class=resicon  title='Delete' onclick=\"editPengalaman('".$bar->namaperusahaan."','".$bar->bidangusaha."','".$bar->bulanmasuk."','".$bar->bulankeluar."','".$bar->bagian."','".$bar->jabatan."','".$bar->alamatperusahaan."','".$bar->gajipokok."','".$bar->alasanberhenti."','".$bar->nomor."');\"></td>
              </tr>";	 	
}
}
else
{
        echo" Error: Incorrect Period";
}
?>
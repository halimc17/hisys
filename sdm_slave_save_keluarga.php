<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$keluarganama	=checkPostGet('keluarganama','');
$keluargajk	=checkPostGet('keluargajk','');
$keluargatmplahir	=checkPostGet('keluargatmplahir','');

$keluargatgllahir	=tanggalsystem(checkPostGet('keluargatgllahir','00-00-0000'));
$keluargapekerjaan	=checkPostGet('keluargapekerjaan','');
$keluargatelp	=checkPostGet('keluargatelp','');
$keluargaemail	=checkPostGet('keluargaemail','');
$karyawanid	=checkPostGet('karyawanid','');
$hubungankeluarga	=checkPostGet('hubungankeluarga','');
$keluargastatus	=checkPostGet('keluargastatus','');
$keluargapendidikan	=checkPostGet('keluargapendidikan','');
$keluargatanggungan	=checkPostGet('keluargatanggungan','');
$keluargabpjstanggungan	=checkPostGet('keluargabpjstanggungan','');
$keluargaemplasment	=checkPostGet('keluargaemplasment','');

$method=checkPostGet('method','');
$karyawanid=checkPostGet('karyawanid','');
$nomor=checkPostGet('nomor','');

if(isset($_POST['del']) or ($keluarganama!='') or isset($_POST['queryonly']))
{
        if(isset($_POST['del']) and $_POST['del']=='true')
        {
                $str="delete from ".$dbname.".sdm_karyawankeluarga where nomor=".$nomor;
        }
        else if(isset($_POST['queryonly']))
        {
                $str="";
        }
        else
        {
                if($method=='insert')
                {
                $str="insert into ".$dbname.".sdm_karyawankeluarga
                     (	`karyawanid`,
                                `nama`,
                                `jeniskelamin`,
                                `tempatlahir`,
                                `tanggallahir`,
                                `hubungankeluarga`,
                                `status`,
                                `levelpendidikan`,
                                `pekerjaan`,
                                `telp`,
                                `email`,
                                `tanggungan`,
								`nobpjstanggungan`,
                                `emplasment`
                          )
                          values(".$karyawanid.",
                          '".$keluarganama."',
                          '".$keluargajk."',
                          '".$keluargatmplahir."',
                          '".$keluargatgllahir."',
                          '".$hubungankeluarga."',
                          '".$keluargastatus."',
                          '".$keluargapendidikan."',
                          '".$keluargapekerjaan."',
                          '".$keluargatelp."',
                          '".$keluargaemail."',
                          '".$keluargatanggungan."',
						  '".$keluargabpjstanggungan."',
                          '".$keluargaemplasment."'
                          )";
                }
                else
                {
             $str="update ".$dbname.".sdm_karyawankeluarga set
                     `karyawanid`=".$karyawanid.",
                                `nama`='".$keluarganama."',
                                `jeniskelamin`='".$keluargajk."',
                                `tempatlahir`='".$keluargatmplahir."',
                                `tanggallahir`=".$keluargatgllahir.",
                                `hubungankeluarga`='".$hubungankeluarga."',
                                `status`='".$keluargastatus."',
                                `levelpendidikan`=".$keluargapendidikan.",
                                `pekerjaan`='".$keluargapekerjaan."',
                                `telp`='".$keluargatelp."',
                                `email`='".$keluargaemail."',
                                `tanggungan`=".$keluargatanggungan.",
								`nobpjstanggungan`=".$keluargabpjstanggungan.",
                                `emplasment`=".$keluargaemplasment."
                                where nomor=".$nomor;	
                }
                // echo($str);
                // exit();
        }
 if($str!=''){
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
 }
        $str="select a.nobpjstanggungan, a.*,case a.tanggungan when 0 then 'N' else 'Y' end as tanggungan1,case a.emplasment when 0 then 'N' else 'Y' end as emplasment1, 
              b.kelompok,COALESCE(ROUND(DATEDIFF('".date('Y-m-d')."',a.tanggallahir)/365.25,1),0) as umur
                  from ".$dbname.".sdm_karyawankeluarga a,".$dbname.".sdm_5pendidikan b
                       where a.karyawanid=".$karyawanid." 
                       and a.levelpendidikan=b.levelpendidikan
                       order by hubungankeluarga";	
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar=$res->fetch())
        {
        $no+=1;
       $val=$bar->hubungankeluarga;
       if($_SESSION['language']=='EN'){
                           switch($bar->hubungankeluarga){
                             case'Pasangan':
                                 $val='Couple';
                                 break;
                             case'Anak':
                                 $val='Child';
                                 break;
                             case'Ibu':
                                 $val='Mother';
                                 break;
                             case'Bapak':
                                 $val='Father';
                                 break;
                             case'Adik':
                                 $val='Younger brother/sister';
                                 break;        
                             case'Kakak':
                                 $val='Older brother/sister';
                                 break;      
                             case'Ibu Mertua':
                                 $val='Monther-in-law';
                                 break;   
                             case'Bapak Mertua':
                                 $val='Father-in-law';
                                 break;   
                             case'Sepupu':
                                 $val='Cousin';
                                 break;  
                             case'Ponakan':
                                 $val='Nephew';
                                 break;                                
                             default:
                                 $val='Foster child';
                                 break;                         
                        }
       }
                       $gal = $bar->status;
            if($_SESSION['language']=='EN' && $bar->status=='Kawin')
              $gal='Married';
          if($_SESSION['language']=='EN' && ($bar->status=='Bujang' or $bar->status=='Lajang'))
                 $gal='Single';                  
        echo"	  <tr class=rowcontent>
                         <td class=firsttd align=center>".$no."</td>
                         <td>".$bar->nama."</td>			  
                         <td>".$bar->jeniskelamin."</td>
                         <td>".$val."</td>			  
                         <td>".$bar->tempatlahir."</td>
						 <td>".tanggalnormal($bar->tanggallahir)."</td>			  
                         <td>".$gal."</td>
                         <td>".$bar->umur."Yrs</td>
                         <td>".$bar->kelompok."</td>
                         <td>".$bar->pekerjaan."</td>
                         <td>".$bar->telp."</td>
                         <td>".$bar->email."</td>
                         <td>".$bar->tanggungan1."</td>
						 <td>".$bar->nobpjstanggungan."</td>	
                         <td>".$bar->emplasment1."</td>
                         <td style='text-align:center;'>
                           <img src=images/skyblue/edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->nama."','".$bar->jeniskelamin."','".$bar->tempatlahir."','".tanggalnormal($bar->tanggallahir)."','".$bar->hubungankeluarga."','".$bar->status."','".$bar->levelpendidikan."','".$bar->pekerjaan."','".$bar->telp."','".$bar->email."','".$bar->tanggungan."','".$bar->nobpjstanggungan."','".$bar->emplasment."','".$bar->nomor."');\"> 
                           <img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"delKeluarga('".$karyawanid."','".$bar->nomor."');\">
                         </td>
                       </tr>";	 	
        }
}
else
{
        echo " Error; Data incomplete";
}
?>

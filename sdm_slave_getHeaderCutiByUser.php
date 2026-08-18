<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
$karyawanid=$_POST['karyawanid'];

        $str1="select * from ".$dbname.".sdm_cutiht a
               where karyawanid =".$karyawanid." order by periodecuti asc"; 
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);

        echo"<table class=sortable cellspacing=1 cellpadding=5 style='width:100%;' border=0>
             <thead>
                 <tr class=rowheader>
                    <td>No</td>	 
                    <td hidden>".$_SESSION['lang']['nokaryawan']."</td>
                    <td>".$_SESSION['lang']['namakaryawan']."</td>		
                        <td>".$_SESSION['lang']['periode']."</td>			
                        <td>".$_SESSION['lang']['dari']."</td>
                        <td>".$_SESSION['lang']['tanggalsampai']."</td>
                        <td>".$_SESSION['lang']['hakcuti']."</td>
                        <td>".$_SESSION['lang']['hakcuti']." Tambahan</td>
                        <td>Adjs Hak Cuti</td>
                        <td>".$_SESSION['lang']['diambil']."</td>
                        <td>".$_SESSION['lang']['sisa']."</td>
                        </tr>
                 </thead>
                 <tbody id=container>"; 
        $no=0;	 
        while($bar1=$res1->fetch())
        {
                $no+=1;

                echo"<tr class=rowcontent id=baris".$no." onlcick=showByUser('".$bar1->karyawanid."',event)>
                           <td>".$no."</td>
                           <td hidden>".$bar1->karyawanid."</td>
                                   <td>".getNamaKaryawan($bar1->karyawanid)."</td>
                                   <td>".$bar1->periodecuti."</td>				   
                                   <td>".tanggalnormal($bar1->dari)."</td>
                                   <td>".tanggalnormal($bar1->sampai)."</td>
                                   <td align=right>".$bar1->hakcuti."</td>
                                   <td align=right>".$bar1->cutitambahan."</td>
                                   <td align=right>".$bar1->adjs_hakcuti."</td>
                                   <td align=right>".$bar1->diambil."</td>
                                   <td align=right>".$bar1->sisa."</td>
                        </tr>	   
                                   ";
        }	 
        echo"	 
                 </tbody>
                 <tfoot>
                 </tfoot>
                 </table>";
?>
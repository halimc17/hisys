<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
		

$method=checkPostGet('method','');
$invoice=checkPostGet('invoice','');
$kurs=checkPostGet('kurs','');
$faktur=checkPostGet('faktur','');
$jenis=checkPostGet('jenis','');
$cariPt=checkPostGet('cariPt','');
$pt=checkPostGet('pt','');

$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$namaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nokontrak=makeOption($dbname, 'keu_penagihanht', 'noinvoice,nokontrak');
switch($method)
{

    case'getFaktur':
       $iFaktur="select * from ".$dbname.".keu_fakturpajak where pt='".$pt."' "
            . " and status = '0' order by nofaktur limit 1";
        $nFaktur=$owlPDO->query($iFaktur) or die(print " Gagal: ".PDOException::getMessage());
        $nFaktur->setFetchMode(PDO::FETCH_ASSOC);
       while($dFaktur=  $nFaktur->fetch())
       {
           $optFaktur.="<option value=".$dFaktur['nofaktur'].">".$dFaktur['nofaktur']."</option>";
       }
       echo $optFaktur;
    break;
        case 'insert':
                $ha="insert into ".$dbname.".pmn_faktur (nofaktur,kodept,atasbiaya,kurs,noinvoice,nokontrak,jenis)
                values ('".$faktur."','".$pt."','Harga Jual','".$kurs."',"
                . "'".$invoice."','".$nokontrak[$invoice]."','".$jenis."')";
                try{
                    $owlPDO->exec($ha); 
                    $updFaktur = "update ".$dbname.".keu_fakturpajak set status='1' where nofaktur='".$faktur."'";
                    try{$owlPDO->exec($updFaktur); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;

case'loadData':
                echo"<div id=container>
                        <table class=sortable cellspacing=1 border=0>
                         <thead>
                                     <tr class=rowheader>
                                        <td align=center>No</td>
                                        <td align=center>No. Faktur</td>
                                         <td align=center>".$_SESSION['lang']['noinvoice']."</td>    
                                             <td align=center>".$_SESSION['lang']['kontrak']."</td> 

                                                 <td align=center style='display:none'>".$_SESSION['lang']['jenis']."</td>  
                                                 <td align=center>".$_SESSION['lang']['kurs']."</td>          
                                        <td align=center>Action</td></tr>
                                     </tr>
                            </thead>
                            <tbody>";

                $limit=15;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
                $maxdisplay=($page*$limit);

                $ql2="select count(*) as jmlhrow from ".$dbname.".pmn_faktur where kodept like '%".$cariPt."%'";// echo $ql2;notran
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch())
                {
                        $jlhbrs= $jsl->jmlhrow;
                }
                
                $iList="select a.*, b.nofaktur from ".$dbname.".pmn_faktur a
                                left join ".$dbname.".keu_fakturpajak b
                                on a.nofaktur = b.nofaktur 
                                where a.kodept like '%".$cariPt."%' 
                                limit ".$offset.",".$limit."";
                $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
                $nList->setFetchMode(PDO::FETCH_ASSOC);
                $no=$maxdisplay;
                while($dList=$nList->fetch())
                {
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$dList['nofaktur']."</td>";
                    echo "<td align=left>".$dList['noinvoice']."</td>";
                    echo "<td align=left>".$nokontrak[$dList['noinvoice']]."</td>";
                    echo "<td align=right style='display:none'>".$dList['jenis']."</td>";
                    echo "<td align=right>".$dList['kurs']."</td>";
                    echo "<td align=center>
                            <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pmn_faktur','".$dList['nofaktur']."','','pmn_slave_pajak_pdf',event);\">

                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['nofaktur']."');\">

                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
                }
                echo"
                <tr class=rowheader><td colspan=18 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";

        case 'delete':

                $tab="delete from ".$dbname.".pmn_faktur where nofaktur='".$faktur."'";
                try{
                    $owlPDO->exec($tab); 
                    $updFaktur = "update ".$dbname.".keu_fakturpajak set status='0' where nofaktur='".$faktur."'";
                    try{$owlPDO->exec($updFaktur); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
        break;

        case 'getFaktur':
                // $optFaktur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sFaktur="select * from ".$dbname.".keu_fakturpajak where status = '0' order by nofaktur limit 1";
                $qFaktur=$owlPDO->query($sFaktur) or die(print " Gagal: ".PDOException::getMessage());
                $qFaktur->setFetchMode(PDO::FETCH_OBJ);
                while ($dFaktur=$qFaktur->fetch())
                {
                        $optFaktur.="<option value=".$dFaktur['nofaktur'].">".$dFaktur['nofaktur']."</option>";
                }
                echo $optFaktur;
        break;
default:
}
?>
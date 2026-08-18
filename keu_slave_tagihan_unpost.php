<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
$proses=$_POST['proses'];
$page=$_POST['page'];
if($page>0)$page=$page; else $page=0;
$noinvoice=$_POST['noinvoice'];

switch($proses)
{
    case'loadData':
    $limit=20;
    $offset=$page*$limit;
    
    $where = "a.kodeorg='".$_SESSION['org']['kodeorganisasi']."' and a.updateby='".$_SESSION['standard']['userid']."'";
    if($_SESSION['empl']['kodejabatan']==5)$where = "a.kodeorg like '%' and a.updateby like '%'"; // manager

    $ql2="select count(*) as jmlhrow from ".$dbname.".keu_tagihanht a where ".$where." and a.posting = 1
        ";
    $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ); 
    while($jsl=$query2->fetch())
        { $jlhbrs= $jsl->jmlhrow; }
    
    //cari nama orang
    $str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ); 
    while($bar=$res->fetch())
    {
       $nama[$bar->karyawanid]=$bar->namakaryawan;
    }   
    //kamus supplier	
    $str2="select * from ".$dbname.".log_5supplier
           where 1";
    $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
    $res2->setFetchMode(PDO::FETCH_ASSOC); 
    while($bar2=$res2->fetch())
    {
        $kamussupplier[$bar2['supplierid']]=$bar2['namasupplier'];
    }
    //kamus dahdibayar	
    $str2="select * from ".$dbname.".aging_sch_vw
           where dibayar>0";
    $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
    $res2->setFetchMode(PDO::FETCH_ASSOC); 
    while($bar2=$res2->fetch())
    {
        $kamusdibayar[$bar2['noinvoice']]=$bar2['dibayar'];
    }    
 
    
    $str="select a.* from ".$dbname.".keu_tagihanht a where ".$where." and a.posting = 1
        order by a.tanggal desc limit ".$offset.",".$limit."";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ); 
		$no=0;
        while($bar=$res->fetch())
        {
            $no+=1;
            echo"<tr class=rowcontent id='tr_".$no."'>
            <td>".$bar->noinvoice."</td>
            <td>".$bar->kodeorg."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>
            <td>".$nama[$bar->updateby]."</td>
            <td>".$bar->nopo."</td>
            <td>".$kamussupplier[$bar->kodesupplier]."</td>
            <td>".$bar->keterangan."</td>
            <td align=right>".number_format($bar->nilaiinvoice)."</td>
            <td>".$nama[$bar->postingby]."</td>
            <!--td>".$bar->posting."</td>
            <td>".(isset($kamusdibayar[$bar->noinvoice])? $kamusdibayar[$bar->noinvoice]: '')."</td-->
            ";
            if(($bar->posting=='1')and (!isset($kamusdibayar[$bar->noinvoice]) or $kamusdibayar[$bar->noinvoice]==0)) // sudah dipost, belum dibayar
                echo"<td><img src=images/application/application_edit.png class=resicon title='Unposting' onclick=unposting('".($bar->noinvoice)."','".$page."');>";
            else // belum posting / sudah dibayar
                echo"<td><img src=images/application/application_delete.png class=resicon  title='Unable to Unpost (Not Posted/Paid)'>";
        }	 	
        echo"
        <tr><td colspan=11 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr>";  

	
    echo"</tbody></table>";        
    break;    
    case'unposting':
        // kalo ada apus, nolin rpalokasi
        $sIns="update ".$dbname.".keu_tagihanht
            set posting='0', postingby='0'
            where noinvoice = '".$noinvoice."'";
      try{
              $owlPDO->exec($sIns); 
          }catch (PDOException $e){
               echo "r Error :" . $e->getMessage();
               exit;
          }
    break;    
    default:
    break;
}
?>
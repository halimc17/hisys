<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$param=$_POST;
if(isset($_GET['proses'])!=''){
    $param['proses']=$_GET['proses'];
}
$optKlmk=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$whr="nopo='".$param['nopo']."'";
$optStat=makeOption($dbname, 'log_poht', 'nopo,statuspo',$whr);

switch($param['proses']){
       case'preview':
           if(count($optStat)==0){
               exit(' Gagal: No. PO '.$param['nopo'].' tidak ditemukan');
           }
           if($optStat[$param['nopo']]!=2){
               exit("error: PO Sudah di Terima di gudang");
           }
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader  align=center>";
        $tab.="<td rowspan=2>".$_SESSION['lang']['nopp']."</td>
         
        <td colspan=4 align=center>Data Saat Ini</td>
        <td colspan=6 align=center>Menjadi</td></tr>";
        $tab.="<tr  align=center><td>".$_SESSION['lang']['kodebarang']."</td>
               <td>".$_SESSION['lang']['namabarang']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlah']."</td>";
        $tab.="<td>".$_SESSION['lang']['satuan']."</td>";
        $tab.="<td>".$_SESSION['lang']['kodebarang']."</td>
               <td>".$_SESSION['lang']['namabarang']."</td>";
        $tab.="<td>".$_SESSION['lang']['satuan']."</td>";
        $tab.="<td>".$_SESSION['lang']['action']."</td></tr>";
        $tab.="</tr></thead><tbody>";
        $sSaldo=$owlPDO->query("select distinct *  from ".$dbname.".log_podt where nopo='".$param['nopo']."'");
        $sSaldo->setFetchMode(PDO::FETCH_ASSOC);
           while($rSaldo=  $sSaldo->fetch()){
               $nod+=1;
               $tab.="<tr class=rowcontent>";
                $tab.="<td id=nopp_".$nod.">".$rSaldo['nopp']."</td>";
                $tab.="<td id=kdBrg_".$nod.">".$rSaldo['kodebarang']."</td>";
                $tab.="<td id=nmBrg_".$nod.">".$optBrg[$rSaldo['kodebarang']]."</td>";
                $tab.="<td align=right  id=jmlhPsn_".$nod.">".$rSaldo['jumlahpesan']."</td>";
                $tab.="<td id=Sat_".$nod.">".$rSaldo['satuan']."</td>";
                $tab.="<td><input type=text id=kdBrgBaru_".$nod." value='' readonly class=myinputtext style=width:100px; onclick=getKdBarang('".$nod."',event) /></td>";
                $tab.="<td id=nmBrgBaru_".$nod."></td>";
                $tab.="<td id=satBaru_".$nod."></td>";
                $tab.="<td align=center><img class=resicon src='images/save.png' onclick=updatePo('".$nod."') /></td>";
                $tab.="</tr>";
         }
         $tab.="</tbody></table><input type=hidden id=nopoUp value='".$param['nopo']."'>";
           echo $tab;
       break;
       case"getForm":
       $tab.="<fieldset><legend>".$_SESSION['lang']['findnoBrg']."</legend>
             Find<input type=text class=myinputtext id=no_brg>
             <button class=mybutton onclick=findBrg(".$param['rowKe'].")>Find</button></fieldset><div id=container></div>";
       echo $tab;
       break;
       case'satDt':
           
           $optSat.="<select id=satUpdate_".$param['rowke']."><option value='".$param['satuan']."'>".$param['satuan']."</option>";
           $sSat=$owlPDO->query("select distinct satuankonversi from ".$dbname.".log_5stkonversi where kodebarang='".$param['kdBarang']."'");
           $sSat->setFetchMode(PDO::FETCH_ASSOC);
           while($rSat=  $sSat->fetch()){
               $optSat.="<option value='".$rSat['satuankonversi']."'>".$rSat['satuankonversi']."</option>";
           }
           $optSat.="</select>";
           echo $optSat;
       break;
       case'updateDt':
         $supdate="update ".$dbname.".log_podt set kodebarang='".$param['kdBarang']."' 
                   where kodebarang='".$param['oldKdBrg']."'  and nopp='".$param['nopp']."'";
        $supdate2="update ".$dbname.".log_prapodt set kodebarang='".$param['kdBarang']."' 
                   where kodebarang='".$param['oldKdBrg']."'  and nopp='".$param['nopp']."'";  
           try{
          $owlPDO->exec($supdate);
          $owlPDO->exec($supdate2);          
  }
  catch (PDOException $e) {
             print " Gagal  !: " . $e->getMessage() . "<br/>";
             die();
      }         
       break;
       case'cariBarangDlmDtBs':
                        $txtfind=$_POST['txtfind'];
           try{
                        $str=$owlPDO->query("select * from ".$dbname.".log_5masterbarang where namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%' ");
                        $str->setFetchMode(PDO::FETCH_OBJ);
                        echo"
          <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; height:300px;\" >
        <table class=data cellspacing=1 cellpadding=2  border=0>
                                 <thead>
                                 <tr class=rowheader>
                                 <td class=firsttd>
                                 No.
                                 </td>
                                 <td>".$_SESSION['lang']['kodebarang']."</td>
                                 <td>".$_SESSION['lang']['namabarang']."</td>
                                 <td>".$_SESSION['lang']['satuan']."</td>
                                 <td>".$_SESSION['lang']['saldo']."</td>
                                 </tr>
                                 </thead>
                                 <tbody>";
                        $no=0;	 
                        while($bar=$str->fetch())
                        {
                                $no+=1;
                //===========================pengambilan saldo
                //ambil saldo barang
                                $saldoqty=0;
                                $str1=$owlPDO->query("select sum(saldoqty) as saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$bar->kodebarang."'
                                       and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'");
                                $str1->setFetchMode(PDO::FETCH_OBJ);
                                while($bar1=$str1->fetch())
                                {
                                        $saldoqty=$bar1->saldoqty;
                                }

                                //ambil pemasukan barang yang belum di posting
                                $qtynotpostedin=0;
                                $str2=$owlPDO->query("select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
                       b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' 
                                           and a.tipetransaksi<5
                                           and a.post=0
                                           group by kodebarang");

                                $str2->setFetchMode(PDO::FETCH_OBJ);
                                while($bar2=$str2->fetch())
                                {
                                        $qtynotpostedin=$bar2->jumlah;
                                }
                                if($qtynotpostedin=='')
                                   $qtynotpostedin=0;


                                //ambil pengeluaran barang yang belum di posting
                                $qtynotposted=0;
                                $str2=$owlPDO->query("select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
                       b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' 
                                           and a.tipetransaksi>4
                                           and a.post=0
                                           group by kodebarang");

                                $str2->setFetchMode(PDO::FETCH_OBJ);
                                while($bar2=$str2->fetch())
                                {
                                        $qtynotposted=$bar2->jumlah;
                                }
                                if($qtynotposted=='')
                                   $qtynotposted=0;

                                $saldoqty=($saldoqty+$qtynotpostedin)-$qtynotposted;
        //============================================		

                                if($bar->inactive==1)
                                {
                                    echo"<tr bgcolor='red' style='cursor:pointer;'  title='Inactive' >";
                                        $bar->namabarang=$bar->namabarang. " [Inactive]";
                                        $bgr=" bgcolor='red'";
                                }
                                else
                                {				
                                    echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."','".$param['rowKe']."')\" title='Click' >";
                                 }   
                                echo" <td class=firsttd >".$no."</td>
                                          <td>".$bar->kodebarang."</td>
                                          <td>".$bar->namabarang."</td>
                                          <td>".$bar->satuan."</td>
                                          <td align=right>".number_format($saldoqty,2,',','.')."</td>
                                         </tr>";
                        }	 
                        echo "</tbody>
                                  <tfoot>
                                  </tfoot>
                                  </table></div></fieldset>";
                  }	
                catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "<br/>";
                            die();
                     }
                break;
       
}
?>
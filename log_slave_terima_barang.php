<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=		$_POST['proses'];
$statInput=		isset($_POST['statInput'])? $_POST['statInput']: '';
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
$notransaksi=	isset($_POST['notransaksi'])? $_POST['notransaksi']: '';
$nodok=			isset($_POST['nodok'])? $_POST['nodok']: '';
$idsupplier=	isset($_POST['idsupplier'])? $_POST['idsupplier']: '';
$tanggal=		isset($_POST['tanggal'])? tanggalsystem($_POST['tanggal']): '';
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
$penerimaId=	isset($_POST['penerimaId'])? $_POST['penerimaId']: '';
$mengetahuiId=	isset($_POST['mengetahuiId'])? $_POST['mengetahuiId']: '';
$qty=			isset($_POST['qty'])? $_POST['qty']: '';
$kodebarang=	isset($_POST['kodebarang'])? $_POST['kodebarang']: '';
$kodegudang=	isset($_POST['kodegudang'])? $_POST['kodegudang']: '';
$satuan=		isset($_POST['satuan'])? $_POST['satuan']: '';//satuan pada master barang
$tex=			isset($_POST['tex'])? $_POST['tex']: '';
$post=			0;
$user=			$_SESSION['standard']['userid'];
$arrStatus=		array("0"=>"Diterima","1"=>"Dikirim");
$optPt=			makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi","tipe='PT'");
$optSupplier=	makeOption($dbname, "log_5supplier", "supplierid,namasupplier","kodekelompok='S001'");
$optNama=		makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan","lokasitugas='".$_SESSION['empl']['lokasitugas']."'");


switch($proses)
{
        

        case'postingData':
            $sUpdate="update ".$dbname.".log_lpbht set post='1',tipetransaksi='1',postedby='".$_SESSION['standard']['userid']."' 
                      where notransaksi='".$notransaksi."'";
            try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

            break;
        case'listData':
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
		$dddCari="";
        if($tex!='')
        {
           $dddCari=" and notransaksi like '%".$tex."%'";
        }
        $sql2="select count(*) as jmlhrow from ".$dbname.".log_lpbht where gudangx='".$_SESSION['empl']['lokasitugas']."' ".$dddCari." order by notransaksi desc ";
        $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ); 

        while($jsl=$query2->fetch()){
        $jlhbrs= $jsl->jmlhrow;
        }

        $sData="select distinct * from ".$dbname.".log_lpbht where gudangx='".$_SESSION['empl']['lokasitugas']."'  ".$dddCari." 
                order by notransaksi desc limit ".$offset.",20";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);        

		$dr='';$tab="";
        while($rData=$qData->fetch())
        {
            $dr++;
            $namaposting='Not Posted';
            if(intval($rData['postedby'])!=0)
            {
                  $stry="select namauser from ".$dbname.".user where karyawanid='".$rData['postedby']."'";
                    $resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
                    $resy->setFetchMode(PDO::FETCH_OBJ); 

                  $bary=$resy->fetch();
                  $namaposting=$bary->namauser;

            }

            if($namaposting=='Not Posted' && $rData['post']==1)
            {
                $namaposting=" Posted By ???";
            }
            if($rData['post']<1)
            {

                //tambahkan tombol edit dan delete
                $add="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editBapb('".$rData['notransaksi']."','".$rData['nopo']."','".tanggalnormal($rData['tanggal'])."','".$rData['idsupplier']."');\">";
                $add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delBapb('".$rData['notransaksi']."');\">";
                $add.="&nbsp <img src=images/hot.png class=resicon  title='posting' onclick=\"postData('".$rData['notransaksi']."');\">";

            //	    $add.="<img src=images/application/book_icon.gif class=resicon  title='Post/Close' onclick=\"postingBapb('".$bar->notransaksi."','".$bar->nopo."');\">";
            }  
            else
            {
                $add='';
            }		
            $tab.="<tr class=rowcontent><td>".$dr."</td>";
            $tab.="<td>".$arrStatus[$rData['tipetransaksi']]."</td>";
            $tab.="<td>".$rData['notransaksi']."</td>";
            $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
            $tab.="<td>".$rData['nopo']."</td>";
            $tab.="<td>".$optSupplier[$rData['idsupplier']]."</td>";
            $tab.="<td>".$optNama[$rData['user']]."</td>";
            $tab.="<td>".$namaposting."</td>";
            $tab.="<td align=center>
             ".$add."
             <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewBapb('".$rData['notransaksi']."',event);\"> 
          </td>";
            $tab.="</tr>";
        }
        $tab.="<tr><td colspan=11 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
           <br>
       <button class=mybutton onclick=cariBapb(".($page-1).");>".$_SESSION['lang']['pref']."</button>
           <button class=mybutton onclick=cariBapb(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
           </td>
           </tr>";
        echo $tab;
        break;
        case'getPo':


        //get notransaksi
        if($statInput==0)
        {
            $arrBln=array("1"=>"I","2"=>"II","3"=>"III","4"=>"IV","5"=>"V","6"=>"VI","7"=>"VII","8"=>"VIII","9"=>"IX","10"=>"X","11"=>"XI","12"=>"XII");
            $bln=intval(date("m"));
            $thnskrng=date("Y");
            $ntrans="/".$arrBln[$bln]."/".date("Y")."/BAPB/MA/".$_SESSION['empl']['lokasitugas'];
            $sCek="select distinct notransaksi from ".$dbname.".log_lpbht where notransaksi like '%".$ntrans."%' order by notransaksi desc";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC); 
            $rCek=$qCek->fetch();
            $awal=substr($rCek['notransaksi'],0,3);
            $awal=intval($awal);
//            $thn=substr($rCek['notransaksi'],14,4); codiang sebelumnya
            $thn=substr($rCek['notransaksi'],-17,4);
            //exit("Error:".$thn."__".$rCek['notransaksi']);
            if($thn!=$thnskrng)
            {
            $awal=1;
            }
            else
            {
            $awal+=1;
            }
            $counter=addZero($awal,3);
            $notrans=$counter."/".$arrBln[$bln]."/".date("Y")."/BAPB/MA/".$_SESSION['empl']['lokasitugas'];

            //get nama supplier
            $sSupplier="select distinct kodesupplier from  ".$dbname.".log_poht where nopo='".$nopo."'";
            $qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
            $qSupplier->setFetchMode(PDO::FETCH_ASSOC); 

            $rSupplier=$qSupplier->fetch();
        }

		$tab="<table class=sortable cellspacing=1 border=0>
		 <thead>
			 <tr class=rowheader>
			   <td>No.</td>
			   <td>".$_SESSION['lang']['kodebarang']."</td>
			   <td>".$_SESSION['lang']['namabarang']."</td>
			   <td>".$_SESSION['lang']['satuan']."</td>
			   <td>".$_SESSION['lang']['sudahditerima']."</td>
			   <td>".$_SESSION['lang']['kuantitaspo']."</td>		   
			   <td>".$_SESSION['lang']['diterima']."</td>
			   <td>".$_SESSION['lang']['keterangan']."</td>
			   <td></td>
			 </tr>
			 </thead><tbody>
			 ";
         $no=0;	 
         //get PO detail for this nopo
         $str="select * from ".$dbname.".log_podt where nopo='".$nopo."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ); 

         while($bar=$res->fetch())
         {

                 $no+=1;
                 $qtypo=$bar->jumlahpesan;
                 $jumlah=$qtypo;//default qty adalah jumlah po
                 $namabarang='';
                 $satuan='';
                 //ambil nama barang dan satuan
                 $str2="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
                $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
                $res2->setFetchMode(PDO::FETCH_OBJ); 

                 while($bar1=$res2->fetch())
                 {
                        $namabarang=$bar1->namabarang;
                        $satuan=$bar1->satuan;
                 }
                 //cek konversi satuan
                 if($satuan!=$bar->satuan)
                 {
                        //konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
                        $str1="select jumlah from ".$dbname.".log_5stkonversi 
                               where darisatuan='".$satuan."' and satuankonversi='".$bar->satuan."'
                               and kodebarang='".$bar->kodebarang."'";
                        $res3=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                        $res3->setFetchMode(PDO::FETCH_OBJ);
                        while($bar2=$res3->fetch())
                        {
                                $jumlah=round($qtypo/$bar2->jumlah);//mengkonversi satuan
                        }	   
                 }

//==================ambil jumlah lalu====================
     $jumlahlalu=0;
//===========khusus untuk edit

		$sddt='';
		$jumlahedit=0;
		//ambil value transaksi
		$strh="select jumlah from ".$dbname.".log_lpbdt where 
			   notransaksi='".$notransaksi."'
				and kodebarang='".$bar->kodebarang."'";
        $resh=$owlPDO->query($strh) or die(print " Gagal: ".PDOException::getMessage());
        $resh->setFetchMode(PDO::FETCH_OBJ);

		$barh=$resh->fetch();
		$jumlahedit=isset($barh->jumlah)? $barh->jumlah: 0;

//++++++++++++++++++++++++++++++
         $strx="select sum(a.jumlah) as jumlah,a.kodebarang as kodebarang 
            from ".$dbname.".log_lpbdt a,
                 ".$dbname.".log_lpbht b
                   where a.notransaksi=b.notransaksi 
                   and b.nopo='".$nopo."' 
               and a.kodebarang='".$bar->kodebarang."'
                   ".$sddt."
                   group by kodebarang";
            $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
            $resx->setFetchMode(PDO::FETCH_OBJ);
                while($barx=$resx->fetch())
                {
                        $jumlahlalu=$barx->jumlah;
                }			 
  
                   $sisa=$jumlah-$jumlahlalu;//jika tidak tampilkan sisa yang belum terima
                if($notransaksi!='' && $jumlahedit==0)//jika bukan barang yang termasuk dalam
                  $disab='disabled';                  //bapb yng di edit maka di disable    
                else
                {  
                if($sisa<=0)
                  $disab='disabled';
                else
                  $disab=''; 
                }

                  $tab.="<tr class=rowcontent>
                   <td>".$no."</td>
                   <td>".$bar->kodebarang."</td>
                   <td>".$namabarang."</td>
                   <td id='sat".$bar->kodebarang."'>".$satuan."</td>
                   <td align=right>".number_format($jumlahlalu,2,'.',',')."</td><input type=hidden value=$jumlahlalu id='jumlal".$bar->kodebarang."'>
                   <td align=right>".number_format($jumlah,2,'.',',')."</td><input type=hidden value=$jumlah id='jumsek".$bar->kodebarang."'>
                   <td><input type=text ".$disab." class=myinputtextnumber id='qty".$bar->kodebarang."' onkeypress=\"return angka_doang(event);\" value='".$sisa."' size=7 maxlength=12 onblur=cekButton(this,'btn".$bar->kodebarang."')></td>
                   <td>".$bar->catatan."</td>
                   <td><button class=mybutton id='btn".$bar->kodebarang."' onclick=saveItemPo('".$bar->kodebarang."') ".$disab.">".$_SESSION['lang']['save']."</button>";"
                 </tr>";	 	
         }
//get karyawan yang lokasi tugas sama atau lokasi tugas sama dengan induk
  $optmengetahui="<option value=''></option>";
  $str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' or lokasitugas='".$_SESSION['org']['induk']."'";	 	 
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);

  while($bar=$res->fetch())
  {
        $optmengetahui.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
  }

        $tab.="</tbody>
             <tfoot>
                 <tr>
                   <td colspan=8 align=center>
                   <button onclick=selesaiBapb() class=mybutton>".$_SESSION['lang']['done']."</button>
                   </td>
                 </tr>
                 </tfoot>
                 </table>
                 ";	
        if($statInput=='0')
        {   echo $notrans."###".$tab."###".$rSupplier['kodesupplier'];}
        else
        {
            $sData="select distinct namapenerima,mengetahui from ".$dbname.".log_lpbht where notransaksi='".$notransaksi."'";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);

            $rData=$qData->fetch();

            echo $tab."###".$rData['namapenerima']."###".$rData['mengetahui'];
        }
        break;
       case'saveData':
           
         $status=0;
         $str="select * from ".$dbname.".log_lpbht where notransaksi='".$nodok."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
         if(owlBaris($res)==1)
         {
                $status=1;
         }

         $str="select * from ".$dbname.".log_lpbdt where notransaksi='".$nodok."'
               and kodebarang='".$kodebarang."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);               
         if(owlBaris($res)>0)
         {
                $status=2;
         }	 

         $str="select * from ".$dbname.".log_lpbht where notransaksi='".$nodok."'
               and post=1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);

         if(owlBaris($res)>0)
         {
                $status=3;
         }
        	
         $sCek="select distinct a.notransaksi from ".$dbname.".log_transaksidt a 
                    left join ".$dbname.".log_transaksiht b on a.notransaksi=b.notransaksi 
                    where kodebarang='".$kodebarang."' and b.nopo='".$nopo."'";
        $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
        $qCek->setFetchMode(PDO::FETCH_ASSOC);
        $rCek=owlBaris($qCek);
                if($rCek>0)
                {
//                 $disab="disabled";
                    $status=0;
                }
        //get other data 
//kode pt dan kurs===================================
        $kurs=1;// default untuk kurs sebagai pengali
        $kodept='';
        $str="select kodeorg,kurs from ".$dbname.".log_poht where nopo='".$nopo."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);        
        while($bar=$res->fetch())
        {
                $kodept=$bar->kodeorg;
                $kurs=$bar->kurs;
        }
//harga satuan base on conversion==============================
        $str="select hargasatuan,jumlahpesan,satuan,matauang,kodebarang from ".$dbname.".log_podt where 
              nopo='".$nopo."' and kodebarang='".$kodebarang."'";
        $jumlahpesan='';
        $hargasatuan=0;
        $matauang='';
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);        
        while($bar=$res->fetch())
        {
                $matauang=$bar->matauang;
                $jumlahpesan=$bar->jumlahpesan;
                $hargasatuan=$bar->hargasatuan;
                //konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
                if($satuan!=$bar->satuan)
                 {
                        $jlhkonversi=1;//tidak nol untuk menhindari devide by zero
                        $str1="select jumlah from ".$dbname.".log_5stkonversi 
                               where darisatuan='".$satuan."' and satuankonversi='".$bar->satuan."'
                               and kodebarang='".$bar->kodebarang."'";
                        $res3=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                        $res3->setFetchMode(PDO::FETCH_OBJ);

                        if(owlBaris($res3)>0)
                        {
                                while($bar2=$res3->fetch())
                                {
                                        $jlhkonversi=$bar2->jumlah;
                                }	
                        }
                        if($jlhkonversi!=0)
                        {
                         $hargasatuan=$bar->hargasatuan*$jlhkonversi;
                        }
                 }
        }

        if($kurs==0 or $matauang=='IDR')
           $kurs=1;
           $hargasatuan=$hargasatuan*$kurs;

//==================ambil jumlah lalu====================
     $jumlahlalu=0;
         $str="select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi 
            from ".$dbname.".log_lpbdt a,
                 ".$dbname.".log_lpbht b
                   where a.notransaksi=b.notransaksi and  
                   b.nopo='".$nopo."' 
                   and a.kodebarang='".$kodebarang."'
                   and a.notransaksi='".$nodok."'
                   order by notransaksi desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
                $jumlahlalu=$bar->jumlah;
        }	   
//===============================================================		 		  
  //periksa apakah sudah ada status 7
  if($status==0 or $status==1 or $status==2)
  {
        $stro="select a.post from ".$dbname.".log_lpbht a
               left join ".$dbname.".log_lpbdt b
                   on a.notransaksi=b.notransaksi
               where a.tanggal>".$tanggal." and a.kodept='".$kodept."'
                   and b.kodebarang='".$kodebarang."' and gudangx='".$_SESSION['empl']['lokasitugas']."'
                   and a.post=1";
        $reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
        $reso->setFetchMode(PDO::FETCH_OBJ);
        if(owlBaris($reso)>0)
        {
                $status=7;
                echo " Error :".$_SESSION['lang']['tanggaltutup'];
                exit(0);
        }	   
  }

//=============================start input/update	
//status=0
//   exit("error: ".$status);
        if($status==0)
        {
            $sql="select nopo from ".$dbname.".log_lpbht where notransaksi='".$nodok."'";
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASOC);

            $rCek=owlbaris($query);
            if($rCek>0){
                $str="update ".$dbname.".log_lpbht set tipetransaksi=0,notransaksi='".$nodok."',
                      tanggal='".$tanggal."',kodept='".$kodept."',nopo='".$nopo."',
                      gudangx='".$_SESSION['empl']['lokasitugas']."',user=".$user.",
                      idsupplier='".$idsupplier."',post=".$post.",namapenerima='".$penerimaId."',
                      mengetahui='".$mengetahuiId."'
                      where notransaksi='".$nodok."'";
            }
            else{
                $str="insert into ".$dbname.".log_lpbht (
                        `tipetransaksi`,`notransaksi`,`tanggal`,
                        `kodept`,`nopo`,`gudangx`,`user`,
                        `idsupplier`,`post`,`namapenerima`,`mengetahui`)
                values('0','".$nodok."',".$tanggal.",
                     '".$kodept."','".$nopo."','".$_SESSION['empl']['lokasitugas']."',".$user.",
                         '".$idsupplier."',".$post.",'".$penerimaId."','".$mengetahuiId."'
                )";	
            }
             try{
                $owlPDO->exec($str); 
                    $str1="insert into ".$dbname.".log_lpbdt (
                      `notransaksi`,`kodebarang`,
                      `satuan`,`jumlah`,`jumlahlalu`)
                      values('".$nodok."','".$kodebarang."',
                      '".$satuan."',".$qty.",".$jumlahlalu.")";
                     try{$owlPDO->exec($str1); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 

            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
            }
            
       
//============================
//status=1
        else if($status==1)
        {
            $str="insert into ".$dbname.".log_lpbdt (
              `notransaksi`,`kodebarang`,
              `satuan`,`jumlah`,`jumlahlalu`)
              values('".$nodok."','".$kodebarang."',
              '".$satuan."',".$qty.",".$jumlahlalu.")";
            try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
        }	
//============================update detail
//status=2
        
        else if($status==2)
        {  
            $str="update ".$dbname.".log_lpbdt set
                  `jumlah`=".$qty.",
                      `updateby`=".$user."
                      where `notransaksi`='".$nodok."'
                      and `kodebarang`='".$kodebarang."'";	  
            $bars=0;
            try{
                $has=$owlPDO->exec($str);
                $bars=$has->rowCount();

            }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            
            if($bars<1)
            {	
                echo " Gagal, (update detail on status 2)".$e->getMessage() ;
            }
            else
            {
                    //update jumlah lalu pada transaksi berikutnya jika ada
                    //ambil no trx yg berikutnya
                    $notrxnext='';
                    $strc="select a.notransaksi as notrx from ".$dbname.".log_lpbdt a, ".$dbname.".log_lpbht b
                          where a.notransaksi= b.notransaksi 
                              and b.nopo='".$nopo."'
                              and a.notransaksi>'".$nodok."'
                              and a.kodebarang='".$kodebarang."'
                              order by notrx asc limit 1";
                    $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
                    $resc->setFetchMode(PDO::FETCH_OBJ);

                    while($barc=$resc->fetch())	
                    {
                            $notrxnext=$barc->notrx;
                    }  

                    if($notrxnext!='')
                    {
                            $str="update ".$dbname.".log_lpbdt set
                          `jumlahlalu`=".$qty.",
                              `updateby`=".$user."
                              where `notransaksi`='".$notrxnext."'
                              and `kodebarang`='".$kodebarang."'";
                            $bars2=0;
                            try{
                                $has2=$owlPDO->exec($str); 
                                $bars2=$has2->rowCount();
                            }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                            if($bars2($conn)<1)
                            {	
                             }
                    }
            }	
               
        }
         
//============================return message
//status=3
        
        if($status==3)
        {	
           echo " Gagal: Data has been posted";
        }
         
       break;
       case'deleteData':
       $sDel="delete from ".$dbname.".log_lpbht where notransaksi='".$notransaksi."'";
            try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
       break;
        default:
        break;
    }
?>
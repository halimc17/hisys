<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

#default Message:
$mess="<html>
               <head>
               </head>
               <body>
               OWL-Preventive Maintenance, remind you for the folowing task:<br>";

# TRAKSI
#1. Ambil barang per masing 
$str="select a.kodebarang,b.namabarang,a.jumlah,a.id,b.satuan from ".$dbname.".schedulerdt a left join 
          ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
            left join ".$dbname.".schedulerht c on a.id=c.id";
$detail=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $detail[$bar->id]['namabarang'][]=$bar->namabarang;
    $detail[$bar->id]['jumlah'][]=$bar->jumlah;
    $detail[$bar->id]['kodebarang'][]=$bar->kodebarang;
    $detail[$bar->id]['satuan'][]=$bar->satuan;
}
#ambil value terakhir
$str="SELECT max(tanggal) as tanggal, id, nilai FROM ".$dbname.".scheduler_aksi group by id";
$lastReminder=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $lastReminder[$bar->id]=$bar->nilai;
}

#2. Ambil HM Traksi Terakhir
$kmAhir=Array();
$str="select * from ".$dbname.".vhc_kmhmakhir_vw order by kodevhc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $kmAhir[$bar->kodevhc]=$bar->kmhmakhir;
}

#3. Ambil HM MESIN PKS;
$str="select sum(hmmesin) as hm, mesin as kodevhc from ".$dbname.".pabrik_hmmesin_vw group by mesin order by mesin";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $kmAhir[$bar->kodevhc]=$bar->hm;
}

$str="select * from ".$dbname.".schedulerht  order by batasreminder asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if($bar->batasreminder==0 or $bar->batasreminder=='')
    {
        #reminder yang sekali tanpa perulangan
        if(date('Y-m-d')==$bar->setiaptanggal and $bar->sekali==2)
        {
                $subject="OWL-Preventive Maintenance ".$bar->kodemesin;
                $mess.="<table>
                                <thead>
                                </thead>
                                <tbody>
                                     <tr><td>Task name</td><td>:".$bar->namatugas."</td></tr>
                                     <tr><td>Object</td><td>:".$bar->kodemesin."</td></tr>
                                     <tr><td>Note</td><td>:".$bar->ketrangan."</td></tr>    
                                     <tr><td>Warning On</td><td>:".tanggalnormal($bar->setiaptanggal)."</td></tr>    
                                </tbody>
                                 </table>";
                if(count($detail[$bar->id]['namabarang'])>0){
                        $mess.="Detail:<br><table border=1><tr><td>Kodebarang</td><td>Nama Barang</td><td>Jumlah</td></tr>";
                           foreach($detail[$bar->id]['namabarang'] as $detil =>$val)
                            {
                                 $mess.="<tr><td>".$detail[$bar->id]['kodebarang'][$detil]."</td><td>".$detail[$bar->id]['namabarang'][$detil]."</td><td>".$detail[$bar->id]['jumlah'][$detil]." ".$detail[$bar->id]['satuan'][$detil]."</td></tr>";                             
                            }
                        $mess.="</table>";   
                }
                $mess.="<br><br>Regards,<br>OWL-Plantation System</body></html>";
                $to=$bar->email;
                if($to!=''){ 
                        $kirim=kirimEmail($to,'',$subject,$mess);#this has return but disobeying;     
               }
                #update table
                $stru="update ".$dbname.".schedulerht set lastreminder='".date('Y-m-d')."' where id=".$bar->id;
                $owlPDO->exec($stru);
                $stri="insert into ".$dbname.".scheduler_aksi(id, tanggal, kodeorg, keterangan, pic, selesai, updateby, nilai)
                            values(".$bar->id.",
                                       '".date('Y-m-d')."',
                                       '".$bar->kodeorg."',
                                       '".$bar->ketrangan."',
                                       '".$bar->email."',0,0,'')";
                $owlPDO->exec($stri);
        }
        #reminder yang  perulangan
        else if(date('m-d')==substr($bar->setiaptanggal,5,5) and $bar->sekali==1)
        {
                $subject="OWL- Preventive Maintenance ".$bar->kodemesin;
                $mess.="<table>
                                <thead>
                                </thead>
                                <tbody>
                                     <tr><td>Task name</td><td>:".$bar->namatugas."</td></tr>
                                     <tr><td>Object</td><td>:".$bar->kodemesin."</td></tr>
                                     <tr><td>Note</td><td>:".$bar->ketrangan."</td></tr>    
                                     <tr><td>Warning On</td><td>:".tanggalnormal($bar->setiaptanggal)."</td></tr>    
                                </tbody>
                                 </table>";
                if(count($detail[$bar->id]['namabarang'])>0){
                        $mess.="Detail:<br><table border=1><tr><td>Kodebarang</td><td>Nama Barang</td><td>Jumlah</td></tr>";
                           foreach($detail[$bar->id]['namabarang'] as $detil =>$val)
                            {
                                 $mess.="<tr><td>".$detail[$bar->id]['kodebarang'][$detil]."</td><td>".$detail[$bar->id]['namabarang'][$detil]."</td><td>".$detail[$bar->id]['jumlah'][$detil]." ".$detail[$bar->id]['satuan'][$detil]."</td></tr>";                             
                            }
                        $mess.="</table>";   
                }
                $mess.="<br><br>Regards,<br>OWL-Plantation System</body></html>";
                $to=$bar->email;
                if($to!=''){ 
                        $kirim=kirimEmail($to,'',$subject,$mess);#this has return but disobeying;     
               }
                #update table
                $stru="update ".$dbname.".schedulerht set lastreminder='".date('Y-m-d')."' where id=".$bar->id;
                $owlPDO->exec($stru);
                $stri="insert into ".$dbname.".scheduler_aksi(id, tanggal, kodeorg, keterangan, pic, selesai, updateby, nilai)
                            values(".$bar->id.",
                                       '".date('Y-m-d')."',
                                       '".$bar->kodeorg."',
                                       '".$bar->ketrangan."',
                                       '".$bar->email."',0,0,'')";
                $owlPDO->exec($stri); 
        }        
    }
    else
    {
        if($bar->tastreminder!='0000-00-00' and $bar->sekali==2)
        {
            #diabaikan karena bukan perulangan dan sudah pernah direminder
        }
        else
        {
            $batasAtas=$bar->batasatas;
            $peringatan=$bar->batasreminder;
            @$saatIni=$kmAhir[$bar->kodemesin];
            if($saatIni=='')
                $saatIni=0;            
            @$peringatanTerakhir=  $lastReminder[$bar->id];            
            if($peringatanTerakhir=='')
                $peringatanTerakhir=0;
            
            #rumus
            @$z=$saatIni%$batasAtas;
             if($z=='')
                 $z=0;
            $akumulasi=$saatIni-$z;
            $sisa=$z;
                      
            if($sisa>=$peringatan and $peringatanTerakhir<$akumulasi)
            {
               $subject="OWL- Preventive Maintenance ".$bar->kodemesin;
                $mess.="<table>
                <thead>
                </thead>
                <tbody>
                     <tr><td>Task name</td><td>:".$bar->namatugas."</td></tr>
                     <tr><td>Object</td><td>:".$bar->kodemesin."</td></tr>
                     <tr><td>Note</td><td>:".$bar->ketrangan."</td></tr>    
                     <tr><td>Warning On</td><td>:".$saatIni." ".$bar->satuan."</td></tr>    
                </tbody>
                 </table>";
                if(count($detail[$bar->id]['namabarang'])>0){
                        $mess.="Detail:<br><table border=1><tr><td>Kodebarang</td><td>Nama Barang</td><td>Jumlah</td></tr>";
                           foreach($detail[$bar->id]['namabarang'] as $detil =>$val)
                            {
                                 $mess.="<tr><td>".$detail[$bar->id]['kodebarang'][$detil]."</td><td>".$detail[$bar->id]['namabarang'][$detil]."</td><td>".$detail[$bar->id]['jumlah'][$detil]." ".$detail[$bar->id]['satuan'][$detil]."</td></tr>";                             
                            }
                        $mess.="</table>";   
                }
                $mess.="<br><br>Regards,<br>OWL-Plantation System</body></html>";
                $to=$bar->email;
                if($to!=''){ 
                        $kirim=kirimEmail($to,'',$subject,$mess);#this has return but disobeying;     
               }
                #update table
                $stru="update ".$dbname.".schedulerht set lastreminder='".date('Y-m-d')."' where id=".$bar->id;
                $owlPDO->exec($stru);
                $stri="insert into ".$dbname.".scheduler_aksi(id, tanggal, kodeorg, keterangan, pic, selesai, updateby, nilai)
                            values(".$bar->id.",
                                       '".date('Y-m-d')."',
                                       '".$bar->kodeorg."',
                                       '".$bar->ketrangan."',
                                       '".$bar->email."',0,0,'".$saatIni."')";
                $owlPDO->exec($stri);       
            }          
        }
    }
    
}	

#reminder  stok minimum
$str="select kodebarang,namabarang,satuan,minstok from ".$dbname.".log_5masterbarang where minstok>0 order by kodebarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $barang[$bar->kodebarang]=$bar->kodebarang;
    $namabarang[$bar->kodebarang]=$bar->namabarang;
    $satuan[$bar->kodebarang]=$bar->satuan;
    $minstok[$bar->kodebarang]=$bar->minstok;
}

#ambil saldo per PT
$str="select sum(saldoqty) as saldo, a.kodebarang, kodeorg,b.minstok from ".$dbname.".log_5masterbarangdt a
          left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where b.minstok>0
          group by kodeorg,a.kodebarang
          having (saldo < minstok or saldo=minstok)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$mess1="<html>
               <head>
               </head>
               <body>
               <br>";
if($numrows>0)
{
    $subject=" OWL- minimum stock reminder (On: ".date('d-m-Y H:i:s').")";
                    $mess1.="Dear All,<br>Berikut ini adalah Material yang sudah mencapai batas minimun. Segera lakukan pengadaan untuk barang:
                        <table border=1 cellspacing=0>
                        <thead>
                         <tr><td>No.</td>
                         <td>PT</td>
                         <td>Kodebarang</td>
                         <td>Nama Barang</td>
                         <td>Satuan</td>
                         <td>Saldo Saat Ini</td>
                         <td>Min.Saldo</td>
                         </tr>   
                        </thead>  
                    <tbody>";
              $no=0;      
              while($bar=$res->fetch())
              {
                  
                 $no+=1;
                  $mess1.="<tr><td>".$no."</td><td>".$bar->kodeorg."</td><td>".$bar->kodebarang."</td>
                                  <td>".$namabarang[$bar->kodebarang]."</td><td>".$satuan[$bar->kodebarang]."</td>
                                   <td align=right>".number_format($bar->saldo,0)."</td><td align=right>".number_format($bar->minstok,0)."</td>
                                    </tr>";   
               } 
             $mess1.="</tbody><tfoot></tfoot></table><br>Regards, <br>OWL-Plantation System<br>The Best and Proven ERP For Palm Oil Plantation Solutions</body></html>";
             
     #ambil email dari reminder stok
             $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='LOGRM'";
             $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
             $res1->setFetchMode(PDO::FETCH_OBJ);
             while($bar=$res1->fetch()){
                 $to1=$bar->nilai;
             }
          #kirim email   
        if($to1!=''){ 
               $kirim=kirimEmail($to1,'',$subject,$mess1);#this has return but disobeying;     
        }
}


##  email ulang tahun:============================================================
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='RCUTI'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $to1=$bar->nilai;
}
             
$str1="select karyawanid,namakaryawan,lokasitugas,email from ".$dbname.".datakaryawan
	       where (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".date('Ymd').")  and tanggallahir like '%".date('m-d')."'
                          and tipekaryawan =0";
$mess2="<html>
               <head>
               </head>
               <body>
               <br>";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res->fetch())
{
        $subject2="Selamat Ulang Tahun !";
        $mess2.="Dear ".$bar1->namakaryawan.",<br><br>
                        Waktu berjalan tiada henti<br> 
                        mengiringi rembulan dan mentari yang terbit nan tenggelam setiap hari<br>
                        mengiringi usiamu yang terus bertambah dari hari ke hari<br>
                        hingga saat ini..<br><br>

                        Selamat ulang tahun ".$bar1->namakaryawan."
                        Sungguh masa depan itu memang ada<br> 
                        karena kau telah berhasil melewati satu 1 tahun lagi masa usiamu.<br><br>

                        Semoga dengan bertambahnya usia menjadikan ".$bar1->namakaryawan." insan yang mulia,<br>
                        semakin bijak dan menjadi berkah bagi lingkungan kehidupan saudara dan 
                        semakin berprestasi dan berkarya di Perusahaan.<br><br>

                        Kami segenap Direksi dan Karyawan mengucapkan SELAMAT ULANG TAHUN<br>
                        Panjang Umur dan Bahagia selalu dalam hidupmu.<br><br><br><br>
                        
                        OWL-Plantation.</body></html>";
       if($bar1->email!==''){
//           $to2.=$to1.",".$bar1->email;
           $to2=$bar1->email;
       } 
       else{
//           $to2=$to1;
           $to2=$to1;
           $to1='';
       }
      if($to2!=''){
//           $kirim=kirimEmail($to2,'',$subject2,$mess2);#this has return but disobeying;            
           $kirim=kirimEmail($to2,$to1,$subject2,$mess2);#this has return but disobeying;            
      }      
      $mess2="<html>
               <head>
               </head>
               <body>
               <br>";
}    


##  email  reminder probation:============================================================
//penentuan tanggal 2 minggu lagi
$t=mktime(0,0,0,date('m'),(date('d')-76),date('Y'));//105 dianggap 3 bulan kurang 2 minggu
$tanggalmasuk=date('Y-m-d',$t);

$str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,subbagian from ".$dbname.".datakaryawan
	       where  tanggalmasuk='".$tanggalmasuk."'  and tipekaryawan =0";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$mess3="<html>
               <head>
               </head>
               <body>
               <br>";
$numrows=owlBaris($res);
if($numrows>0)
{
    $subject3=" Reminder Akhir Masa Percobaan Karyawan Baru (On: ".date('d-m-Y H:i:s').")";
                    $mess3.="Dear Hrd,<br>Berikut ini adalah karyawan yang akan berakhir masa percobannya:
                        <table border=1 cellspacing=0>
                        <thead>
                         <tr><td>No.</td>
                         <td>Nama Naryawan</td>
                         <td>TMK</td>
                         <td>Lokasi Tugas</td>
                         <td>Sub.Bagian</td>
                         </tr>   
                        </thead>  
                    <tbody>";
              $no=0;      
              while($bar=$res->count())
              {
                  
                 $no+=1;
                  $mess3.="<tr><td>".$no."</td>
                                  <td>".$bar->namakaryawan."</td>
                                  <td>".tanggalnormal($bar->tanggalmasuk)."</td>
                                  <td>".$bar->lokasitugas."</td><td>".$bar->subbagian."</td>
                                    </tr>";   
               } 
             $mess3.="</tbody><tfoot></tfoot></table><br>
                               Silahkan diproses sesuai dengan tahapan yang berlaku.<br>
                             <br>Regards, <br>OWL-Plantation System<br>The Best and Proven ERP For Palm Oil Plantation Solutions</body></html>";
          #kirim email   
        if($to1!=''){ 
                $kirim=kirimEmail($to1,'',$subject3,$mess3);#this has return but disobeying;     
        }    
}    


##  email  reminder akhir kontrak karyawan:============================================================
             $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='KONTRAK'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                 $to2=$bar->nilai;
             }
//penentuan tanggal 2 minggu lagi
$t=mktime(0,0,0,date('m'),(date('d')+14),date('Y'));
$tanggalkeluar=date('Y-m-d',$t);

$str1="select karyawanid,namakaryawan,tanggalmasuk,tanggalkeluar,lokasitugas,subbagian from ".$dbname.".datakaryawan
	       where  tanggalkeluar='".$tanggalkeluar."'  and tipekaryawan in(6,2)";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$mess4="<html>
               <head>
               </head>
               <body>
               <br>";
$numrows=owlBaris($res);
if($numrows>0)
{
    $subject4=" Reminder Akhir Masa Kontrak Karyawan (On: ".date('d-m-Y H:i:s').")";
                    $mess4.="Dear Hrd,<br>Berikut ini adalah karyawan yang akan berakhir masa kontraknya:
                        <table border=1 cellspacing=0>
                        <thead>
                         <tr><td>No.</td>
                         <td>Nama Naryawan</td>
                         <td>TMK</td>
                         <td>Lokasi Tugas</td>
                         <td>Sub.Bagian</td>
                         <td>Akhir.Kontrak</td>
                         </tr>   
                        </thead>  
                    <tbody>";
              $no=0;      
              while($bar=$res->fetch())
              {
                  
                 $no+=1;
                  $mess4.="<tr><td>".$no."</td>
                                  <td>".$bar->namakaryawan."</td>
                                  <td>".tanggalnormal($bar->tanggalmasuk)."</td>
                                  <td>".$bar->lokasitugas."</td>
                                  <td>".$bar->subbagian."</td>
                                  <td>".tanggalnormal($bar->tanggalkeluar)."</td>    
                                    </tr>";   
               } 
             $mess4.="</tbody><tfoot></tfoot></table><br>
                               Silahkan diproses sesuai dengan tahapan yang berlaku.<br>
                             <br>Regards, <br>OWL-Plantation System<br>The Best and Proven ERP For Palm Oil Plantation Solutions</body></html>";
          #kirim email   
        if($to2!=''){ 
                $kirim=kirimEmail($to2,'',$subject4,$mess4);#this has return but disobeying;     
        }    
}    

##  email  reminder karyawan yang akan pensiun:============================================================
             $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='RCUTI'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                 $to2=$bar->nilai;
             }
//penentuan umur 55 tahun di 4 bulan kedepan
$t=mktime(0,0,0,date('m')+4,(date('d')),date('Y')-55);
$tanggallahir=date('Y-m-d',$t);

$str1="select karyawanid,namakaryawan,tanggallahir,tanggalmasuk,lokasitugas,subbagian from ".$dbname.".datakaryawan
	       where  tanggallahir='".$tanggallahir."' and tipekaryawan in(0,1,2,3,7,8) and tanggalkeluar='0000-00-00'";
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$mess4="<html>
               <head>
               </head>
               <body>
               <br>";
$numrows=owlBaris($res);
if($numrows>0)
{
    $subject4=" Reminder Masa Pensiun Karyawan (On: ".date('d-m-Y H:i:s').")";
                    $mess4.="Dear Hrd,<br>Berikut ini adalah karyawan yang akan pensiun di tahun ini:
                        <table border=1 cellspacing=0>
                        <thead>
                         <tr><td>No.</td>
                         <td>Nama Naryawan</td>
						 <td>Tgl.Lahir</td>
                         <td>TMK</td>
                         <td>Lokasi Tugas</td>
                         <td>Sub.Bagian</td>
                         </tr>   
                        </thead>  
                    <tbody>";
              $no=0;      
              while($bar=$res->fetch())
              {
                  
                 $no+=1;
                  $mess4.="<tr><td>".$no."</td>
                                  <td>".$bar->namakaryawan."</td>
								  <td>".tanggalnormal($bar->tanggallahir)."</td>
                                  <td>".tanggalnormal($bar->tanggalmasuk)."</td>
                                  <td>".$bar->lokasitugas."</td>
                                  <td>".$bar->subbagian."</td>   
                                    </tr>";   
               } 
             $mess4.="</tbody><tfoot></tfoot></table><br>
                               Silahkan diproses sesuai dengan tahapan yang berlaku.<br>
                             <br>Regards, <br>OWL-Plantation System<br>The Best and Proven ERP For Palm Oil Plantation Solutions</body></html>";
          #kirim email   
        if($to2!=''){ 
                $kirim=kirimEmail($to2,'',$subject4,$mess4);#this has return but disobeying;     
        }    
}    



##  email  reminder janjang angin:============================================================
             $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='RB01'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                 $to2=$bar->nilai;
             }
             
$tanggalkeluar=date('Y-m-d', strtotime( '-2 days' ));
$tanggalkeluar1=date('Y-m-d', strtotime( '-3 days' ));
$tanggalkeluar2=date('Y-m-d', strtotime( '-4 days' ));

        $query = "SELECT left(blok,6) as afdeling, sum(jjgmasak) as jjgmasak
            FROM ".$dbname.".`kebun_taksasi`
            WHERE `tanggal` like '".$tanggalkeluar."%' GROUP BY left(blok,6) ORDER BY left(blok,6)";
            $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);        
        while($rDetail=$qDetail->fetch())
        {
            
            $janjangtaksasi[$rDetail['afdeling']]=$rDetail['jjgmasak'];
            $afdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
        }
        $query = "SELECT left(blok,6) as afdeling, sum(jjgmasak) as jjgmasak
            FROM ".$dbname.".`kebun_taksasi`
            WHERE `tanggal` like '".$tanggalkeluar1."%' GROUP BY left(blok,6) ORDER BY left(blok,6)";
            $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);     
        while($rDetail=$qDetail->fetch())
        {
            
            $janjangtaksasi1[$rDetail['afdeling']]=$rDetail['jjgmasak'];
            $afdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
        }
        $query = "SELECT left(blok,6) as afdeling, sum(jjgmasak) as jjgmasak
            FROM ".$dbname.".`kebun_taksasi`
            WHERE `tanggal` like '".$tanggalkeluar2."%' GROUP BY left(blok,6) ORDER BY left(blok,6)";
            $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);  
        while($rDetail=$qDetail->fetch())
        {
            
            $janjangtaksasi2[$rDetail['afdeling']]=$rDetail['jjgmasak'];
            $afdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
        }
        
        $query = "SELECT left(kodeorg,6) as afdeling, sum(hasilkerja) as janjangpanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` like '".$tanggalkeluar."%' GROUP BY left(kodeorg,6) ORDER BY left(kodeorg,6)";        
            $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);  
        while($rDetail=$qDetail->fetch())
        {
            $janjangpanen[$rDetail['afdeling']]=$rDetail['janjangpanen'];
            $afdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
        }                          
        $query = "SELECT left(kodeorg,6) as afdeling, sum(hasilkerja) as janjangpanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` like '".$tanggalkeluar1."%' GROUP BY left(kodeorg,6) ORDER BY left(kodeorg,6)";        
             $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);  
        while($rDetail=$qDetail->fetch())
        {
            $janjangpanen1[$rDetail['afdeling']]=$rDetail['janjangpanen'];
            $afdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
        }                          
        $query = "SELECT left(kodeorg,6) as afdeling, sum(hasilkerja) as janjangpanen
            FROM ".$dbname.".`kebun_prestasi_vw`
            WHERE `tanggal` like '".$tanggalkeluar2."%' GROUP BY left(kodeorg,6) ORDER BY left(kodeorg,6)";        
             $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);  
        while($rDetail=$qDetail->fetch())
        {
            $janjangpanen2[$rDetail['afdeling']]=$rDetail['janjangpanen'];
            $afdeling[$rDetail['afdeling']]=$rDetail['afdeling'];
        }                          
        
        $tab='';
        if(is_array($afdeling)){
        foreach($afdeling as $key=>$nilai){
            if((($janjangtaksasi2[$nilai]*1.1<$janjangpanen2[$nilai])or($janjangtaksasi2[$nilai]*0.9>$janjangpanen2[$nilai]))or
                (($janjangtaksasi1[$nilai]*1.1<$janjangpanen1[$nilai])or($janjangtaksasi1[$nilai]*0.9>$janjangpanen1[$nilai]))or
                (($janjangtaksasi[$nilai]*1.1<$janjangpanen[$nilai])or($janjangtaksasi[$nilai]*0.9>$janjangpanen[$nilai]))){
//                @$selisih=(abs($janjangtaksasi[$nilai]-$janjangpanen[$nilai]))/$janjangtaksasi[$nilai]*100;
                $warna2="";
                $warna1="";
                $warna="";
                @$selisih2=(abs($janjangtaksasi2[$nilai]-$janjangpanen2[$nilai]))/$janjangtaksasi2[$nilai]*100;
                if($selisih2>10)$warna2=" bgcolor=red";
                @$selisih1=(abs($janjangtaksasi1[$nilai]-$janjangpanen1[$nilai]))/$janjangtaksasi1[$nilai]*100;
                if($selisih1>10)$warna1=" bgcolor=red";
                @$selisih=(abs($janjangtaksasi[$nilai]-$janjangpanen[$nilai]))/$janjangtaksasi[$nilai]*100;
                if($selisih>10)$warna=" bgcolor=red";
                $tab.="<tr>";
                $tab.="<td>".$nilai."</td>";
                $tab.="<td align=right>".number_format($janjangtaksasi2[$nilai])."</td>
                    <td align=right>".number_format($janjangpanen2[$nilai])."</td>
                    <td align=right".$warna2.">".number_format($selisih2,2)."</td>";
                $tab.="<td align=right>".number_format($janjangtaksasi1[$nilai])."</td>
                    <td align=right>".number_format($janjangpanen1[$nilai])."</td>
                    <td align=right".$warna1.">".number_format($selisih1,2)."</td>";
                $tab.="<td align=right>".number_format($janjangtaksasi[$nilai])."</td>
                    <td align=right>".number_format($janjangpanen[$nilai])."</td>
                    <td align=right".$warna.">".number_format($selisih,2)."</td>";
                $tab.="</tr>";
            }
        }
        }
        
$mess4="<html>
               <head>
               </head>
               <body>
               <br>";
if($tab!=''){

    $subject4=" Reminder Varian Taksasi dan Realisasi Panen (On: ".tanggalnormal($tanggalkeluar2)." - ".tanggalnormal($tanggalkeluar).")";
                    $mess4.="Dear All,<br>Berikut ini adalah data realisasi panen pada ".tanggalnormal($tanggalkeluar2)." - ".tanggalnormal($tanggalkeluar)." dengan toleransi realisasi vs taksasi +/- 10%:
                        <table border=1 cellspacing=0>
                        <thead>
                         <tr><td rowspan=2>Afdeling</td><td colspan=3 align=center>".tanggalnormal($tanggalkeluar2)."</td><td colspan=3 align=center>".tanggalnormal($tanggalkeluar1)."</td><td colspan=3 align=center>".tanggalnormal($tanggalkeluar)."</td></tr>
                         <tr>
                         <td>Taksasi (JJG)</td>
                         <td>Panen (JJG)</td>
                         <td>Selisih (%)</td>
                         <td>Taksasi (JJG)</td>
                         <td>Panen (JJG)</td>
                         <td>Selisih (%)</td>
                         <td>Taksasi (JJG)</td>
                         <td>Panen (JJG)</td>
                         <td>Selisih (%)</td>
                         </tr>   
                        </thead>  
                    <tbody>";
             $mess4.=$tab;       
             $mess4.="</tbody><tfoot></tfoot></table><br>
                               Demikian informasi ini disampaikan, semoga berguna.<br>
                             <br>Regards, <br>OWL-Plantation System<br>The Best and Proven ERP For Palm Oil Plantation Solutions</body></html>";
          #kirim email   
        if($to2!=''){ 
                $kirim=kirimEmail($to2,'',$subject4,$mess4);#this has return but disobeying;     
        }    
//        echo $mess4;
}    

#update no.kounter jurnal
$str="update ".$dbname.".keu_5kelompokjurnal set nokounter=1 where nokounter>999";
$owlPDO->exec($str);
?>
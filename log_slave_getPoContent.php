<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
if(isTransactionPeriod())//check if transaction period is normal
{
	$nopo    = isset($_POST['nopo'])? $_POST['nopo']: '';
	$gudang  = isset($_POST['gudang'])? $_POST['gudang']: '';
	$datatype= isset($_POST['tipedata'])? $_POST['tipedata']: '';
	$expnopo = explode('/',$nopo);
	$jnspo   = substr($expnopo[3],0,2);
	$tanggal = checkPostGet('tanggal','');

  $nosj = checkPostGet('nosj','');
  $isinyaaa=$nosj;
	// exit("warning".$nosj);
  if($jnspo=='SO'){
		exit("warning : No. PO tidak ditemukan");
	}


  $getDataPo = "select * from ".$dbname.".setup_parameterappl where kodeparameter='POSOLAR'";
  $barDtPo = fetchData($getDataPo);
  $kodeSolar = $barDtPo[0]['nilai'];

  $strPoSolar = "select distinct(kodebarang) from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$kodeSolar."'";
  $barPoSolar = fetchData($strPoSolar);

  // $getPoSolar = "select * from log_5masterbarang where kodebarang='".$barPoSolar[0]['kodebarang']."'";
  // $resPoSolar = fetchData($getPoSolar);

  // exit("warning".print_r(count($barPoSolar)));

  // if($jnspo == 'PO' && $barPoSolar[0]['kodebarang'] == '351010003') {
  //     exit("Warning : Lengkapi No Surat Jalan untuk transaksi PO Solar");
  // }
  // exit("warning".print_r($barPoSolar[0]['kodebarang']));
  if(count($barPoSolar) > 0) {
  // exit("warning dsini".$_POST['nosj']);
    // $iseng=$nosj;
    if($nosj==''){
      exit("Warning : Lengkapi No Surat Jalan untuk transaksi PO Solar".$_POST['nosj']);
    }
  } 
	
	validasiInput(substr($gudang,0,4),substr($param['blok'],0,6),'GR',tanggalsystemn($tanggal),$exit='1');
	
	//cek pemilik PO
	$ptgudang='';
	$str="select induk from ".$dbname.".organisasi where kodeorganisasi = '".substr($gudang,0,4)."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$ptgudang=$bar->induk;
	}

	#= cek PO tersebut tipe apa? PO/CO/SO/NO ?
	#= hanya tipe inventory saja yang diperbolehkan
	
	$str="select kodeorg,tipepo from ".$dbname.".log_poht where nopo = '".$nopo."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$ptPO=$bar->kodeorg;
		$tipepo=$bar->tipepo;
	}
	
	if($tipepo!='PO'){
		exit("Warningsistem : Transaksi untuk PO ini adalah jenis ".$tipepo.", hanya tipe PO inventory yand diperbolehkan");
	}
	
	if(($ptgudang!=$ptPO)and($ptgudang!='')){
		// exit("warning: belongs to other company (storage:".$ptgudang." << PO:".$ptPO.")");
		exit("warning: EN : belongs to other company (storage:".$ptgudang." << PO:".$ptPO.") | IND : PT pemilik gudang tidak sama dengan PT di-PO (Gudang :".$ptgudang." << PO:".$ptPO.")  ");
	}

  //cek PO apakah sudah status OK(1) disetujui
  $statuspo='x';
  $str="select statuspo,kodesupplier,stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  if(owlBaris($res)>0)
  {
    while($bar=$res->fetch())
    {
      $statuspo=$bar->statuspo;
      $kodesupplier=$bar->kodesupplier;
      $stat_release=$bar->stat_release;
    }

    if($statuspo>0 && $stat_release==1)
    {
      if($datatype=='supplier'){
        echo $kodesupplier;
      }else if($datatype=='data'){
        createForm($nopo);
      }else if($datatype=='edit'){
        $notransaksi=$_POST['notransaksi'];
        createForm($nopo,$notransaksi);	
      }  
    }
    else
    {
      echo" Error: Purchase order no.".$nopo.". not released";
    }
  }
  else
  {
    echo" Error: Purchase order no.".$nopo.". not found";
  }
}
else
{
  echo " Error: Transaction Period missing";
}

function createForm($nopo,$notransaksi='')
{
    //no transaksi terisi hanya pada saat edit
    global $dbname;
    global $conn;
		global $owlPDO;
		
		$tab="";
		
    $tab.="<table class=sortable cellspacing=1 border=0 >
         <thead>
         <tr class=rowheader>
           <th align=center>No.</th>
           <th align=center>".$_SESSION['lang']['kodebarang']."</th>
           <th align=center>".$_SESSION['lang']['namabarang']."</th>
           <th align=center>".$_SESSION['lang']['satuan']."</th>
           <th align=center>".$_SESSION['lang']['sudahditerima']."</th>
           <th align=center>".$_SESSION['lang']['kuantitaspo']."</th>		   
           <th align=center width=75px >".$_SESSION['lang']['diterima']."</th>
           <th align=center>".$_SESSION['lang']['keterangan']."</th>
           <th align=center>".$_SESSION['lang']['nopp']."</th>
           <th align=center>".$_SESSION['lang']['catatan']."</th>
           <th align=center colspan=2>Action</th>
         </tr>
         </thead><tbody>";
				
  	$subgdg = substr($_POST['gudang'],0,4);
  	$countApp = getCountApproval('GR',$subgdg);
				 
    $no=0;	 
    //get PO detail for this nopo
    $str="select * from ".$dbname.".log_podt where nopo='".$nopo."'";
	 // $str="select * from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang like '3%' ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $no+=1;
        $qtypo=($bar->jumlahpesan-$bar->jmlhstlhclose);
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
              $jumlah=round(($qtypo/$bar2->jumlah),6);//mengkonversi satuan
          }	   
        }

//==================ambil jumlah lalu====================
     $jumlahlalu=0;
//===========khusus untuk edit
         $sddt='';
         $jumlahedit=0;
         //ambil value transaksi
         $strh="select jumlah,catatan from ".$dbname.".log_transaksidt where 
                notransaksi='".$notransaksi."'
                        and kodebarang='".$bar->kodebarang."'";
		 $resh=$owlPDO->query($strh) or die(print " Gagal: ".PDOException::getMessage());
		 $resh->setFetchMode(PDO::FETCH_OBJ);
         while($barh=$resh->fetch())
          {
				$catatanedit=$barh->catatan;
                $jumlahedit=$barh->jumlah;
          }		 

         if($notransaksi!='')
           {//khusus untuk edit, jumlah lalu tidak termasuk jumlah yg di edit
                 $sddt=" and a.notransaksi!='".$notransaksi."' ";
           }
//++++++++++++++++++++++++++++++
		 $strx="select sum(a.jumlah) as jumlah from ".$dbname.".log_transaksidt a,
                 ".$dbname.".log_transaksiht b
                   where a.notransaksi=b.notransaksi 
                   and b.nopo='".$nopo."' and b.statusjurnal='1'
               and a.kodebarang='".$bar->kodebarang."' and b.tipetransaksi='6'
                   ".$sddt."
                   group by kodebarang";
		$resx=fetchdata($strx);
		$jlhretur = ($resx[0]['jumlah']==''?0:$resx[0]['jumlah']);
				   
         $strx="select sum(a.jumlah) as jumlah,a.kodebarang as kodebarang 
            from ".$dbname.".log_transaksidt a,
                 ".$dbname.".log_transaksiht b
                   where a.notransaksi=b.notransaksi 
                   and b.nopo='".$nopo."' and b.hasilpersetujuan1!='2'
               and a.kodebarang='".$bar->kodebarang."' and b.tipetransaksi='1'
                   ".$sddt."
                   group by kodebarang";
				
				$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_OBJ);
                while($barx=$resx->fetch())
                {
                        $jumlahlalu=($barx->jumlah)-$jlhretur;
                }			 

                if($notransaksi!='')//jika proses edit
                   $sisa=$jumlahedit;//tampilkan value data yang di edit
                else  
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
                $xyz=$jumlah-$jumlahlalu;
                 $tab.="<tr class=rowcontent>
                   <td align=center>".$no."</td>
                   <td align=center>".$bar->kodebarang."</td>
                   <td>".$namabarang."</td>
                   <td  align=center id='sat".$bar->kodebarang."_".$bar->nopp."'>".$satuan."</td>
                   <td align=right>".number_format($jumlahlalu,2,'.',',')."</td>
                   <td align=right>".number_format($jumlah,2,'.',',')."</td>
                   <td align=center style=max-width:60px ><input type=text ".$disab." class=myinputtextnumber id='qty".$bar->kodebarang."_".$bar->nopp."' onkeypress=\"return angka_doang(event);\" value='".$sisa."' style=width:70px maxlength=12 onblur=cekButton(this,'btn".$bar->kodebarang."')></td>
                   <td>".$bar->catatan."</td>
                   <td>".$bar->nopp."</td>
                    <td><input type=text class=myinputtext id='catatan".$bar->kodebarang."_".$bar->nopp."' value='".$catatanedit."' onkeypress=\"return_tanpa_kutip(event);\"></td>
				   <td align=center><button class=mybutton id='btn".$bar->kodebarang."' onclick=\"saveItemPo('".$bar->kodebarang."','".$xyz."','".$bar->nopp."','".$countApp."')\" ".$disab.">".$_SESSION['lang']['save']."</button></td><td>
                    <button class=mybutton onclick=showupload(event,'".$bar->kodebarang."')>Upload Files</button>
                       
                 </tr>";	 	
         }
// <input type='file' name='upload_".$bar->kodebarang."' id='upload_".$bar->kodebarang."'>
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
             <tfoot colspan=2>

                 
                 <tr>
					<td colspan=10></td><td align=center>
						<button onclick=selesaiBapb() class=mybutton>".$_SESSION['lang']['done']."</button>
					</td><td></td>
                 </tr>
                 </tfoot>
                 </table>
                 ";
	
	
	$lbl.="<fieldset style=float:left>
		<legend>".$_SESSION['lang']['persetujuan']."</legend>
		<table>";
	if($notransaksi!='')
	{
		for($i=1;$i<=$countApp;$i++)
		{
			$arrDetail = detailApprove($i,$notransaksi,'GR');
  			$optKry="<option value='".$arrDetail['karyawanid']."'>".$arrDetail['nama']." [".$arrDetail['idlokasitugas']."]</option>";
  			
  			$lbl.="<tr>
  				<td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
  				<td>:</td>
  				<td>
  					<select id='persetujuan".$i."' disbaled>".$optKry."</select>
  				</td>
  			</tr>";
		}
	}
	else
	{
		for($i=1;$i<=$countApp;$i++)
		{
      $optKry='';
			$arrListApp = listApprove($i,'GR',$subgdg);
			foreach($arrListApp as $key=>$val)
			{
        // if($_SESSION['empl']['lokasitugas']==$val['lokasitugas']){
  				$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
        // }
			}
      $lbl.="<tr>
          <td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
          <td>:</td>
          <td>
            <select id='persetujuan".$i."' style='width:200px'>".$optKry."</select>
            <img id='persetujuan".$i."' onclick=z.elSearch('persetujuan1',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
          </td>
          </tr>";
		}
	}
	$lbl.="</table>
	</fieldset>";
	
	echo $tab."####".$lbl;

        // echo"<table class=sortable cellspacing=1 border=0 >
        //      <thead>
        //          <tr class=rowheader>
        //            <td align=center>No.</td>
        //            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
        //            <td align=center>".$_SESSION['lang']['namabarang']."</td>
        //            <td align=center>".$_SESSION['lang']['satuan']."</td>
        //            <td align=center>".$_SESSION['lang']['sudahditerima']."</td>
        //            <td align=center>".$_SESSION['lang']['kuantitaspo']."</td>      
        //            <td align=center width=75px >".$_SESSION['lang']['diterima']."</td>
        //            <td align=center>".$_SESSION['lang']['keterangan']."</td>
        //            <td align=center>".$_SESSION['lang']['nopp']."</td>
        //    <td align=center>".$_SESSION['lang']['catatan']."</td>
        //            <td align=center>Action</td>
        //          </tr>
        //          </thead><tbody>
        //          ";	  

}
?>
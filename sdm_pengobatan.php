<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pengobatan.js'></script>
<link rel=stylesheet type=text/css href=style/payrollHO.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper('MEDICAL CLAIM').'</span>');
$optthn="<option value=''></option>";
for($x=-1;$x<4;$x++)
{
	$optthn.="<option value='".(date('Y')-$x)."'>".(date('Y')-$x)."</option>";
}

$optperiode="<option value=''></option>";
// for($x=-1;$x<36;$x++)
// {
    // $t=mktime(0,0,0,date('m')-$x,15,date('Y'));
	// $optperiode.="<option value='".date('Y-m',$t)."'>".date('m-Y',$t)."</option>";
// }
$str="select distinct(periode) as periode from ".$dbname.".sdm_pengobatanht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optperiode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}


$arrsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

//ambil data karyawan=============================

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
	$str="select karyawanid,nik,namakaryawan,lokasitugas from ".$dbname.".datakaryawan where
	      alokasi=1 order by namakaryawan";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$str="select karyawanid,nik,namakaryawan,subbagian,lokasitugas from ".$dbname.".datakaryawan where lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe not in ('HOLDING')) and alokasi=0 order by namakaryawan";	
}else{
	$str="select karyawanid,nik,namakaryawan,subbagian,lokasitugas from ".$dbname.".datakaryawan where
	      alokasi=0 and lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan";	
}

$optKar="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optKar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." - ".$bar->nik." - ".$bar->lokasitugas."</option>";
}
//===================================
//yang berobat

$optKel="<option value=0>Ybs/PIC</option>";

//===================================
//ambil jenis pengobatan
$str="select * from ".$dbname.".sdm_5jenisbiayapengobatan order by nama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optJns='';
while($bar=$res->fetch())
{
    $optJns.="<option value='".$bar->kode."'>".$bar->nama."</option>";
}

//=====================================
//ambil rumah sakit
$str="select supplierid,namasupplier from ".$dbname.".log_5supplier where kodekelompok='S006' order by namasupplier asc";
//$str="select id,namars,kota from ".$dbname.".sdm_5rs order by namars";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optRs='';
$optRs.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
	//$optRs.="<option value='".$bar->id."'>".$bar->namars."[".$bar->kota."]</option>";
    $optRs.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}
//================================================
//ambil list diagnosa
$optDiagnosa='';
$optDiagnosa.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".sdm_5diagnosa order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optDiagnosa.="<option value='".$bar->id."'>".$bar->kode." - ".$bar->diagnosa."</option>";
}
//===============================================
//jenis klaim
$optklaim="<option value=0>".$_SESSION['lang']['karyawan']."</option>
          <option value=1>".$_SESSION['lang']['rumahsakit']."</option>
          <option value=2>".$_SESSION['lang']['internal']." Clinic</option>";

//================================================
//loaksi tugas
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='RO') {
    $strd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
            length(kodeorganisasi)=4 and induk='".$_SESSION['org']['kodeorganisasi']."' order by kodeorganisasi";
}else if(substr($_SESSION['empl']['lokasitugas'],2,2)=='LO'){//LO pakai regional saja
    $strd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment
	where regional='".$_SESSION['empl']['regional']."')  order by kodeorganisasi";
	/*
	 $strd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
	induk='".$_SESSION['empl']['kodeorganisasi']."' and length(kodeorganisasi)=4 and tipe not in ('HOLDING','KANWIL') order by kodeorganisasi";
	*/
	
} else {
    $strd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
            length(kodeorganisasi)=4 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
}
$resd=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
$resd->setFetchMode(PDO::FETCH_OBJ);
$lokasitugas="<option value=''></option>";
while($bard=$resd->fetch())
{
    $lokasitugas.="<option value='".$bard->kodeorganisasi."'>".$bard->kodeorganisasi." - ".$bard->namaorganisasi."</option>";
}

//option periode akuntansi
$optx='';
for($x=-1;$x<13;$x++)
{
    $dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
    $optx.="<option value='".date('Y-m',$dt)."'>".date('m-Y',$dt)."</option>";
}
   
$arr[0]="<fieldset><legend>".$_SESSION['lang']['form']."</legend>
        <fieldset><table  border=0>
	 <tr>
	  <td style=width:130px >".$_SESSION['lang']['thnplafon']."</td>
	  <td><select style=width:55px id=thnplafon onchange=getTrxNumber(this.options[this.selectedIndex].value)>".$optthn."</select>
		  ".$_SESSION['lang']['periode']."<select id=periode>".$optx."</select></td>
	  <td style=width:120px>".$_SESSION['lang']['notransaksi']."</td>
	  <td style=width:320px><input style=width:195px type=text class=myinputtext id=notransaksi maxlength=20 disabled></td>
	 </tr>
	  <tr>
	  <td>".$_SESSION['lang']['tanggalkwitansi']."</td>
	  <td style=width:150px><input type=text id=tanggalkwitansi value='".date('d-m-Y')."' size=10 maxlength=10 onkeypress=\"return false\" onmouseover=setCalendar(this) class=myinputtext></td>
	  <td>".$_SESSION['lang']['tanggalpengajuan']."</td>
	  <td style=width:150px><input type=text id=tanggalpengajuan disabled value='".date('d-m-Y')."' size=10 maxlength=10 onkeypress=\"return false\" onmouseover=setCalendar(this) class=myinputtext></td>
	 </tr>
	 <tr>
	  <td>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
	  <td><select id=jenisbiaya onchange=getFamily() style='width:170px;'>".$optJns."</select> </td>	 
	  <td>".$_SESSION['lang']['lokasitugas']."</td>
	  <td><select style=width:200px id=lokasitugas onchange=loadOptkar(this.options[this.selectedIndex].value)>".$lokasitugas."</select></td>
	 <tr>
	  <td>".$_SESSION['lang']['namakaryawan']."</td>
	  <td style='width:200px;'><select id=karyawanid style='width:170px;' onchange=\"getFamily();\">".$optKar."</select>
	      <img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		  <input type=hidden value=insert id=method></td>
	 
                    
	<td>".$_SESSION['lang']['plafon']." (Rp)</td>
	<td><input style='width:85px;' type=text id=plafon class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value='0' maxlength=10 disabled>
			<label id='satuanPlafon'></label>
	</td>	
	 </tr>
         <tr>
          <input type=hidden class=myinputtext id=gaji maxlength=20><input type=hidden class=myinputtext id=tipekaryawan>
          <input type=hidden class=myinputtext id=sudahbayar><input type=hidden class=myinputtext id=blmbayar>
	  <td>".$_SESSION['lang']['yangberobat']."</td>
	  <td><select id=ygberobat style='width:170px;'>".$optKel."</select> </td>	 
	  <td>".$_SESSION['lang']['rumahsakit']."</td>
	  <td><select id=rs style='width:200px;'>".$optRs."</select>
		  <img id='rs' onclick=z.elSearch('rs',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	  </td>
	 </tr>
	 <tr>
	  <td>".$_SESSION['lang']['diagnosa']."</td>
	  <td><select id=diagnosa style='width:170px;'>".$optDiagnosa."</select>
		  <img id='diagnosa' onclick=z.elSearch('diagnosa',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	  </td>	  	 
	  <td>".$_SESSION['lang']['klaim']."</td>
	  <td><select id=klaim style='width:200px;'>".$optklaim."</select></td>
	 </tr>
	 <tr>
	  <td>".$_SESSION['lang']['jumlahhariinap']."</td>
	  <td><input type=text class=myinputtext id=hariistirahat value=0 onkeypress=\"return angka_doang(event);\" maxlength=3 style='width:40px;'>
	      ".$_SESSION['lang']['tanggal']."<input type=text id=tanggal value='".date('d-m-Y')."' size=8 maxlength=10 onkeypress=\"return false\" onmouseover=setCalendar(this) class=myinputtext>
	  </td>	  	 
	  <td>".$_SESSION['lang']['keterangan']."</td>
	  <td><input type=text class=myinputtext id=keterangan onkeypress=\"return tanpa_kutip(event);\" style='width:195px;' ></td>
	 </tr>
	 </table></fieldset>
	 <fieldset>
	  <legend>".$_SESSION['lang']['biayabiaya']."</legend>
	  <fieldset style=float:left><legend>".$_SESSION['lang']['total']."</legend>
	  <table border=0>
	  <tr>
	    <td>".$_SESSION['lang']['biayars']."</td><td><input type=text id=byrs class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=10 value=0 onblur=\"change_number(this);calculateTotal();\"></td>
	    <td>".$_SESSION['lang']['biayapendaftaran']."</td><td><input type=text id=byadmin class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=10 value=0 onblur=\"change_number(this);calculateTotal();\"></td>		
	  </tr>
	  <tr>
	    <td>".$_SESSION['lang']['biayalab']."</td><td><input type=text id=bylab class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=10 value=0 onblur=\"change_number(this);calculateTotal();\"></td>
	    <td>".$_SESSION['lang']['biayaobat']."</td><td><input type=text id=byobat class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=10 value=0 onblur=\"change_number(this);calculateTotal();\"></td>		 
	  </tr>	
	  <tr>
	    <td>".$_SESSION['lang']['biayadr']."</td><td><input type=text id=bydr class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=10 value=0 onblur=\"change_number(this);calculateTotal();\"></td>	 
            <td>".$_SESSION['lang']['totalbiaya']."</td><td><input type=text id=total disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=15 value=0></td>	 
	  </tr>		  	   
	  </table></fieldset>
	  <fieldset style=float:left><legend>".$_SESSION['lang']['beban']."</legend>
                           <table>
                              <tr><td>".$_SESSION['lang']['perusahaan']."</td><td><input type=text class=myinputtextnumber id=bebanperusahaan onkeypress=\"return false;\" disabled sise=12 value=0></td></tr>
                              <tr><td>".$_SESSION['lang']['karyawan']."</td><td><input type=text class=myinputtextnumber id=bebankaryawan onkeypress=\"return angka_doang(event);\"  sise=12 value=0 onblur=\"change_number(this);calculateTotal();\"></td></tr>
                              <tr><td>BPJS</td><td><input type=text class=myinputtextnumber id=bebanjamsostek onkeypress=\"return angka_doang(event);\"  sise=12 value=0 onblur=\"change_number(this);calculateTotal();\"></td></tr>
                           </table>
                      </fieldset>
	 </fieldset> 
	<input type=button class=mybutton value='".$_SESSION['lang']['save']."' onclick=savePengobatan() id=mainsavebtn>
	<input type=button  class=mybutton value='".$_SESSION['lang']['new']."' onclick=clearForm();>
	</fieldset>";
   
$arr[1]="<fieldset style=width:400px>
	  <legend>".$_SESSION['lang']['obatobat']."</legend>
	  <table>
	  <tr>
	    <td>".$_SESSION['lang']['namaobat']."</td>
		<td><input style=width:150px type=text id=namaobat class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=45></td></tr>
		 <td>".$_SESSION['lang']['jenis']."</td><td ><select style=width:155px id=jenisobat><option value=Paten>Paten</option><option value=Generic>Generic</option></select></td>	  </tr>
	  <tr><td><td>
	  <input type=button class=mybutton value='".$_SESSION['lang']['save']."' onclick=saveObat()>
	  <input type=button class=mybutton value='".$_SESSION['lang']['selesai']."' onclick=selesai()>
	  </table></fieldset><fieldset style=width:400px>
	    <legend>".$_SESSION['lang']['list']."</legend>
		<div>
		 <table class=sortable cellspacing=1 border=0>
		  <thead>
		   <tr class=rowheader>
		    <td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['namaobat']."</td>
			<td align=center>".$_SESSION['lang']['jenis']."</td>
			<td align=center>Action</td>
		   </tr>
		  </thead>
		  <tbody id=container1>
		  </tbody>
		  <tfoot>
		  </tfoot>
		 </table>
		</div>
	  </fieldset>
	 </fieldset> 	 
	 ";
//ambil daftar pengobatan dengan tahun sekarang
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='LO' || substr($_SESSION['empl']['lokasitugas'],2,2)=='RO'){
	$where='';
}else{
	$where=" and karyawanid='".$_SESSION['standard']['userid']."' ";
}




if(substr($_SESSION['empl']['lokasitugas'],2,2)=='RO') {
    $orgin="select kodeorganisasi from ".$dbname.".organisasi where 
            length(kodeorganisasi)=4 and induk='".$_SESSION['org']['kodeorganisasi']."'";
}else if(substr($_SESSION['empl']['lokasitugas'],2,2)=='LO'){
    $orgin="select kodeunit from ".$dbname.".bgt_regional_assignment
	where regional='".$_SESSION['empl']['regional']."'";
} else {
    $orgin="select kodeorganisasi from ".$dbname.".organisasi where 
            length(kodeorganisasi)=4 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	$karid=" and a.karyawanid='".$_SESSION['standard']['userid']."'";
	
}








    $str2="select a.*,c.namakaryawan,d.diagnosa as ketdiag, a.notransaksi as notransaksi,
          a.karyawanid as karyawanid,a.kodebiaya as kodebiaya,a.keterangan as keterangan,
          c.lokasitugas as lokasitugas,a.tahunplafon as thnplafon,a.periode as periode,
          a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
          a.bypendaftaran as byadmin,a.ygsakit as ygsakit,a.tanggal as tanggal,a.totalklaim as totalklaim,
          a.jlhhariistirahat as istirahat,a.bebankaryawan as bebankaryawan,a.bebanjamsostek as bebanjamsostek,
          a.bebanperusahaan as bebanperusahaan,a.diagnosa as diagnosa,a.klaimoleh as klaim
          from ".$dbname.".sdm_pengobatanht a 
  
          left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
          left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
          where a.periode='".date('Y-m')."' 
          and a.kodeorg in (".$orgin.") ".$karid."
          order by a.updatetime desc, a.tanggal desc";
		  
		
$arr[2]="<fieldset>
	  <legend>".$_SESSION['lang']['list']."</legend>
      <div style='width:1220px;height:450px;overflow:auto;'>
	  ".$_SESSION['lang']['periode'].":<select id=optplafon onchange=loadPengobatan(this.options[this.selectedIndex].value)>".$optperiode."</select>
	   <img src='images/excel.jpg' onclick='printRekapKlaim()' class='resicon'>
	  <table class=sortable cellspacing=1 border=0 width=2000px>
	  <thead>
	    <tr class=rowheader>
                  <td align=center width=55>".$_SESSION['lang']['action']."</td>
                <td align=center width=25>No</td>
                <td align=center width=100>".$_SESSION['lang']['notransaksi']."</td>
                <td align=center width=50>".$_SESSION['lang']['periode']."</td>
                <td align=center width=70>".$_SESSION['lang']['tanggal']."</td>
                <td align=center width=200>".$_SESSION['lang']['namakaryawan']."</td>
                <td align=center width=150>".$_SESSION['lang']['rumahsakit']."</td>
                <td align=center width=50>".$_SESSION['lang']['jenisbiayapengobatan']."</td>		  
				  <td align=center width=90>Biaya Rumah Sakit</td>
				  <td align=center width=90>Biaya Pendaftaran</td>  
				  <td align=center width=90>Biaya Lab.</td>  
				  <td align=center width=90>Biaya Obat</td>  
				  <td align=center width=90>Jasa Dokter</td>
				  <td align=center width=90>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['perusahaan']."</td>
				  <td align=center width=90>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['karyawan']."</td>
				  <td align=center width=90>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['bpjs']."</td>    
				<td align=center>".$_SESSION['lang']['diagnosa']."</td>
				<td align=center width=90>".$_SESSION['lang']['total']."</td>
				<td align=center width=90>".$_SESSION['lang']['dibayar']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
                  </tr>
                    </thead>
	  <tbody id='container'>";
	  $namaBiaya = makeOption($dbname,'sdm_5jenisbiayapengobatan','kode,nama');
	  $no=0;
	  $regional = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
	  $golonganKar = makeOption($dbname,'datakaryawan','karyawanid,kodegolongan');
      
		// echo $str2;
	  $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	  $res2->setFetchMode(PDO::FETCH_OBJ);          
	  while($bar2=$res2->fetch())
	  {
                            $sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$bar2->kodebiaya."' and kodegolongan='".			$golonganKar[$bar2->karyawanid]."' and regional = '".$regional[$bar2->lokasitugas]."'";
                            $qPlaf=$owlPDO->query($sPlaf) or die(print " Gagal: ".PDOException::getMessage());
                            $numRowsPlaf = owlBaris($qPlaf);
							$qPlaf->setFetchMode(PDO::FETCH_ASSOC); 
                            $rPlaf=$qPlaf->fetch();
                            if($rPlaf['satuan']==4){
                                    $vWhere = " and tahunplafon between '".(($bar2->thnplafon)-2)."' and '".$bar2->thnplafon."'";
                            }else{
                                    $vWhere = " and tahunplafon='".$bar2->thnplafon."'";
                            } 

                            $sPlaf2="select sum(jlhbayar) as jlhbayar, sum(bebanperusahaan) as bebanperusahaan, kodebiaya from ".$dbname.".sdm_pengobatanht
                                              where karyawanid='".$bar2->karyawanid."' and kodebiaya='".$bar2->kodebiaya."' ".$vWhere." 
                                              group by kodebiaya";
                            $qPlaf2=$owlPDO->query($sPlaf2) or die(print " Gagal: ".PDOException::getMessage());
                            $qPlaf2->setFetchMode(PDO::FETCH_ASSOC); 
                            $rPlaf2=$qPlaf2->fetch();

                            $gaji="select * from ".$dbname.".sdm_5gajipokok where karyawanid = ".$bar2->karyawanid."
                               and tahun like ".$bar2->thnplafon."";
                            $hasil=$owlPDO->query($gaji) or die(print " Gagal: ".PDOException::getMessage());
                            $hasil->setFetchMode(PDO::FETCH_ASSOC); 
                            $row=$hasil->fetch();
                            $jumlahgaji=$row['jumlah'];

                            if($bar2->kodebiaya=='RWJLN'){
                                    $hasilPlaf=$jumlahgaji-($rPlaf2['bebanperusahaan']-$bar2->bebanperusahaan);
                            }else if($bar2->kodebiaya=='RWINP'){
                                    $hasilPlaf=$rPlaf['rupiah'];
                            }else if($rPlaf['satuan']==4){
                                    $hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar2->bebanperusahaan);
                            }else if($rPlaf['satuan']==3){
                                    $hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar2->bebanperusahaan);
                            }else{
                                    if($numRowsPlaf <= 0){
                                            $hasilPlaf='0';
                                    }else{
                                            if($rPlaf2['jlhbayar'] >= $rPlaf['rupiah']){
                                                    $hasilPlaf='0';
                                            }else{
                                                    $hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar2->bebanperusahaan);
                                            }
                                    }
                            }
		
	   $no+=1;
	   $arr[2].="<tr class=rowcontent>
	   <td align=center>";
	   
	   if($bar2->posting==0)
	   {
               $ket=rawurlencode($bar2->keterangan);
               $arr[2].="<img src=images/edit.png title='edit' class=resicon onclick=editPengobatan('".$bar2->notransaksi."','".$bar2->karyawanid."','".$bar2->kodebiaya."','".$bar2->lokasitugas."','".$bar2->thnplafon."','".$bar2->periode."','".$bar2->rs."','".$bar2->byrs."','".$bar2->bydr."','".$bar2->bylab."','".$bar2->byobat."','".$bar2->byadmin."','".$bar2->ygsakit."','".$bar2->diagnosa."','".tanggalnormal($bar2->tanggal)."','".$bar2->totalklaim."','".$bar2->istirahat."','".$bar2->bebankaryawan."','".$bar2->bebanjamsostek."','".$bar2->bebanperusahaan."','".$bar2->klaim."','".$ket."','".tanggalnormal($bar2->tanggalkwitansi)."','".tanggalnormal($bar2->tanggalpengajuan)."','".number_format($hasilPlaf,2)."','".$rPlaf['satuan']."')>";
               $arr[2].="&nbsp<img src=images/close.png class=resicon  title='delete' onclick=deletePengobatan('".$bar2->notransaksi."')>";
           }
            $arr[2].="&nbsp<img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan('".$bar2->notransaksi."',event)>";

            $arr[2].="</td><td align=center>".$no."</td>
                <td>".$bar2->notransaksi."</td>
                <td>".substr($bar2->periode,5,2)."-".substr($bar2->periode,0,4)."</td>
                <td>".tanggalnormal($bar2->tanggal)."</td>
                <td>".$bar2->namakaryawan."</td>
                <td>".$arrsup[$bar2->rs]."</td>
                <td>".$namaBiaya[$bar2->kodebiaya]."</td>
                  <td align=right>".number_format($bar2->byrs,2,'.',',')."</td>
                  <td align=right>".number_format($bar2->byadmin,2,'.',',')."</td>
                  <td align=right>".number_format($bar2->bylab,2,'.',',')."</td>
                  <td align=right>".number_format($bar2->byobat,2,'.',',')."</td>
                  <td align=right>".number_format($bar2->bydr,2,'.',',')."</td>
                    <td align=right>".number_format($bar2->bebanperusahaan,2,'.',',')."</td>
                  <td align=right>".number_format($bar2->bebankaryawan,2,'.',',')."</td>
                  <td align=right>".number_format($bar2->bebanjamsostek,2,'.',',')."</td>    
                <td>".$bar2->ketdiag."</td>
                <td align=right>".number_format($bar2->totalklaim,2,'.',',')."</td>
                <td align=right>".number_format($bar2->jlhbayar,2,'.',',')."</td>
                <td>".$bar2->keterangan."</td>
              </tr>";	  	
        }
$arr[2].="</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	 </div>
	 </fieldset><iframe id=frmku frameborder=0 style='width:0px;height:0px;'></iframe>	 
	 ";	 
$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['obatobat'];
$hfrm[2]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$arr,100,'100%');
CLOSE_BOX();
echo close_body();
?>
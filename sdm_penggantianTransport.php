<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/sdm_jatahBBM.js></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['penggantiantransport']).'</span>');

$optthn="<option value=''></option>";
for($x=-1;$x<10;$x++){
	$mk=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optthn.="<option value='".(date('Y-m',$mk))."'>".(date('m-Y',$mk))."</option>";
}
//===============ambil list karyawan
$str="select a.namakaryawan,a.karyawanid, b.namajabatan from ".$dbname.".datakaryawan a
      left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
	  where (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".date('Y-m-d')."')  and statuskaryawan != 'Keluar'  and lokasitugas='".$_SESSION['empl']['lokasitugas']."'
	  order by a.namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKaryawan='';
while($bar=$res->fetch()){
	@$optKaryawan.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." ".$bar->nik." [ ".$bar->namajabatan." ]</option>";
}	  
//pt==================
$str="select kodeorganisasi,namaorganisasi from 
      ".$dbname.".organisasi where tipe='pt'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optPt='';
while($bar=$res->fetch()){
	$optPt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
//================================	  
$frm[0]="<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
	 <table border=0 style='display: inline-block;vertical-align:top'>
	   <tr>
	      <td>".$_SESSION['lang']['periode']."</td>
		  <td><select id=periode onchange=getNotransaksi(this.options[this.selectedIndex].value) style='width:75px;'>".$optthn."</select></td>
	      <td>".$_SESSION['lang']['notransaksi']."</td>
		  <td><input type=text class=myinputtext id=notransaksi size=15 disabled style='width:195px;'></td>		     
	   </tr>
	   <tr>
	      <td>".$_SESSION['lang']['karyawan']."</td>
		  <td><select id=karyawanid  style='width:200px;'>".$optKaryawan."</select>
		  <img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		  </td>		  
	      <td>".$_SESSION['lang']['alokasibiaya']."</td>
		  <td><select id=pt  style='width:200px;'>".$optPt."</select></td>		  
	   </tr>
	   <tr>
	      <td>".$_SESSION['lang']['keterangan']."</td>
		  <td><input type=text id=keterangan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=45  style='width:196px;'></td>		    
		  
		  
		  <td>".$_SESSION['lang']['harga']."</td>
		  <td><input type=text id=harga class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=45  style='width:75px;'> / Liter</td>
		  
	   </tr>
	 <tr><td>
		</td>
		<td  colspan=4>
			<input type=hidden value=insert id=method>
			<button class=mybutton  id='savebtn' onclick=saveBBM();>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancelBBM()>".$_SESSION['lang']['new']."</button>
		</td>
     </table>
	 <table  border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
	 <td align=left style='padding-left:15px;vertical-align:top' rowspan='4' colspan=3>
		<fieldset style='float:left;'>
			<legend>Upload File</legend>
			<table class=sortable cellspacing=1 border=0>
				<thead> 
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['namafile']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id=containerupload></tbody>
				<tbody>
				<tr>
					<td colspan=2>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
					<td style='text-align:center'>
						<img src=images/plus.png class=resicon id='btnsubmit'  title='Add File ' onclick=\"submitfile();\">
					</td>
				</tr>
				</tbody>
			</table>
			</fieldset>
		</td>
	 </table>
	 "; 
//==============================
$frm[0].="<table border=0>
	   <tr>
	      <!-- <td>".$_SESSION['lang']['transport']."</td> -->
		  <td><input type=hidden class=myinputtextnumber id=bytransport size=15 maxlength=12 onkeypress=\"return angka_doang(event);\" onblur=\"change_number(this);calculateTotal();\" value=0></td>
	      <!-- <td>".$_SESSION['lang']['perawatan']."</td> -->
		  <td><input type=hidden class=myinputtextnumber id=byperawatan size=15 maxlength=12 onkeypress=\"return angka_doang(event);\" onblur=\"change_number(this);calculateTotal();\" value=0></td>		     
	   </tr>
	   <tr>
	      <!-- <td>".$_SESSION['lang']['toll']."</td> -->
		  <td><input type=hidden class=myinputtextnumber id=bytoll size=15 maxlength=12 onkeypress=\"return angka_doang(event);\" onblur=\"change_number(this);calculateTotal();\" value=0></td>		  
	      <!-- <td>".$_SESSION['lang']['lain']."</td> -->
		  <td><input type=hidden class=myinputtextnumber id=bylain size=15 maxlength=12 onkeypress=\"return angka_doang(event);\" onblur=\"change_number(this);calculateTotal();\" value=0>
		  <!-- Total --><input type=hidden id=total disabled class=myinputtextnumber size=15>
		  </td>		  
	   </tr>
	   
	 </table>
     </fieldset>
	 <tr><td colspan=4>
	 </table>"; 
//======================================
$frm[0].="<div style='clear:both'></div><fieldset>
     <legend>".$_SESSION['lang']['detail']." ".$_SESSION['lang']['biaya']."</legend>
	 <table>
	   <tr>
	      <td>".$_SESSION['lang']['tanggal']."</td>
		  <td><input type=text class=myinputtext id=tanggal onmouseover=setCalendar(this) size=7 maxlength=12 onkeypress=\"return false;\"></td>
		  <td>".$_SESSION['lang']['vhc_kmhm_awal']." </td>
		  <td><input type=text class=myinputtextnumber id=kmawal size=5 maxlength=5 onkeypress=\"return angka_doang(event);\" value=0></td>
		  <td>".$_SESSION['lang']['vhc_kmhm_akhir']." </td>
		  <td><input type=text class=myinputtextnumber id=kmakhir size=5 maxlength=5 onkeypress=\"return angka_doang(event);\" value=0></td>
	      <td>".$_SESSION['lang']['vhc_jumlah_bbm']." (Ltr)</td>
		  <td><input type=text class=myinputtextnumber id=jlhbbm  onchange=getrupiah() size=5 maxlength=5 onkeypress=\"return angka_doang(event);\" value=0></td>		     
	      <td>".$_SESSION['lang']['rupiah']."</td>
		  <td><input type=text class=myinputtextnumber id=totalharga size=7 maxlength=8 onkeypress=\"return angka_doang(event);\" value=0></td>
	      <td><button class=mybutton onclick=saveLitre()>".$_SESSION['lang']['save']."</button></td>
	   </tr>
	 </table>
     <div style='width:850px;height:150px; overflow:auto;'>
	  <table cellspacing=1 border=0 class=sortable style='min-width:800px'>
	  <thead>
	  <tr class=rowheader>
	     <td align=center>No</td>
		 <td align=center>".$_SESSION['lang']['tanggal']."</td>
		 <td align=center>".$_SESSION['lang']['vhc_kmhm_awal']." (Ltr)</td>
		 <td align=center>".$_SESSION['lang']['vhc_kmhm_akhir']." (Ltr)</td>
		 <td align=center>".$_SESSION['lang']['jumlah']." (Ltr)</td>
		 <td align=center>".$_SESSION['lang']['total']." (Rp)</td>
		 <td align=center>Action</td>
	  </thead>
	  <tbody id=containerSolar>
	  
	  </tbody>
	  <tfoot>
	  </tfoot>
	  </table>
	 </div>	 
     </fieldset>	
     </fieldset>";	
//=====================================
$frm[1]="<fieldset>
     <legend>".$_SESSION['lang']['list']."</legend>
	 Periode<select id=periox onchange=getData(this.options[this.selectedIndex].value)>".$optthn."</select>
	 <img src='images/pdf.jpg' class=resicon onclick=previewBBMPeriode(event) title='view'>
	 <div style='min-width;800px;height:450px;overflow:auto;'>
	 <table class=sortable cellspacing=1 border=0>
	 <thead>
	   <tr class=rowheader>
	     <td align=center>No.</td>
		 <td align=center>".$_SESSION['lang']['notransaksi']."</td>
		 <td align=center>".$_SESSION['lang']['periode']."</td>
		 <td align=center>".$_SESSION['lang']['pt']."</td>
		 <td align=center>".$_SESSION['lang']['karyawan']."</td>
		 <td align=center hidden>".$_SESSION['lang']['totalbiaya']."</td>
		 <td align=center>".$_SESSION['lang']['dibayar']."</td>
		 <td align=center>".$_SESSION['lang']['tanggalbayar']."</td>
		 <td align=center>".$_SESSION['lang']['vhc_jumlah_bbm']."</td>
		 <td align=center>".$_SESSION['lang']['keterangan']."</td>	
		 <td align=center>".$_SESSION['lang']['action']."</td>	 
	   </tr>
	 </thead>
	 <tbody id=container>";

$str="select a.*,sum(b.jlhbbm) as bbm,c.namakaryawan from ".$dbname.".sdm_penggantiantransport a
      left join ".$dbname.".sdm_penggantiantransportdt b 
	  on a.notransaksi=b.notransaksi
	  left join ".$dbname.".datakaryawan c
	  on a.karyawanid=c.karyawanid
	   where periode='".date('Y-m')."' and 
	  kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
	  group by notransaksi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$res->fetch()){
	$no+=1;
	$add='';
	if($bar->posting==0){
		$add.=" <img src='images/application/application_edit.png' class=resicon onclick=\"editBBM('".$bar->notransaksi."','".substr($bar->periode,0,4)."-".substr($bar->periode,5,2)."','".$bar->alokasi."','".$bar->karyawanid."','".$bar->totalklaim."','".$bar->bbm."','".$bar->keterangan."')\" title='edit'>";
		$add.=" <img src='images/application/application_delete.png' class=resicon onclick=deleteBBM('".$bar->notransaksi."') title='delete'>";
	}
		$add.=" <img src='images/pdf.jpg' class=resicon onclick=previewBBM('".$bar->notransaksi."',event) title='view'>";


	$frm[1].="<tr class=rowcontent>
	     <td align=center>".$no."</td>
		 <td>".$bar->notransaksi."</td>
		 <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
		 <td>".$bar->alokasi."</td>
		 <td>".$bar->namakaryawan."</td>
		 <td hidden align=right>".number_format($bar->totalklaim,2,',','.')."</td>
		 <td align=right>".number_format($bar->dibayar,2,',','.')."</td>
		 <td>".tanggalnormal($bar->tanggalbayar)."</td>
		 <td align=right>".number_format($bar->bbm,2,',','.')."</td>
		 <td>".$bar->keterangan."</td>	
		 <td>".$add."</td>	 
	   </tr>";	
}	  	 
$frm[1].="</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	 </div>
     </fieldset>";	 	 
//============================================
$frm[2]="<fieldset>
     <legend>".$_SESSION['lang']['pembayaran']."</legend>
	 <div style='width;700px;height:300px;overflow:auto;'>
	 <table class=sortable cellspacing=1 border=0>
	 <thead>
	   <tr class=rowheader>
	     <td align=center>No.</td>
		 <td align=center>".$_SESSION['lang']['notransaksi']."</td>
		 <td align=center>".$_SESSION['lang']['periode']."</td>
		 <td align=center>".$_SESSION['lang']['pt']."</td>
		 <td align=center>".$_SESSION['lang']['karyawan']."</td>
		 <td align=center hidden>".$_SESSION['lang']['totalbiaya']."</td>
		 <td align=center>".$_SESSION['lang']['dibayar']."</td>
		 <td align=center>".$_SESSION['lang']['tanggalbayar']."</td>
		 <td align=center>Action</td>	 
	   </tr>
	 </thead>
	 <tbody id=containerbayar>";
$str2="select a.*,sum(b.jlhbbm) as bbm,c.namakaryawan from ".$dbname.".sdm_penggantiantransport a
      left join ".$dbname.".sdm_penggantiantransportdt b 
	  on a.notransaksi=b.notransaksi
	  left join ".$dbname.".datakaryawan c
	  on a.karyawanid=c.karyawanid
	   where 
	    a.posting=0 and
	  a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
	  group by notransaksi";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$res2->fetch()){
	$no+=1;
	$frm[2].="<tr class=rowcontent>
	     <td align=center>".$no."</td>
		 <td>".$bar->notransaksi."</td>
		 <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
		 <td>".$bar->alokasi."</td>
		 <td>".$bar->namakaryawan."</td>
		 <td align=right hidden>".number_format($bar->totalklaim,2,',','.')."</td>
		 <td align=right><img src='images/puzz.png' style='cursor:pointer;' title='click to get value' onclick=\"document.getElementById('bayar".$no."').value=".$bar->totalklaim."\">
		  <input type=text id=bayar".$no." class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=12 onblur=change_number(this) size=12></td>
		 <td><input type=text id=tglbayar".$no." class=myinputtext onkeypress=\"return false;\" maxlength=10  size=10 onmouseover=setCalendar(this) value='".date('d-m-Y')."'></td>
	     <td align=center><img src='images/save.png' title='Save' class=resicon onclick=saveBBMClaim('".$no."','".$bar->notransaksi."')></td>
	   </tr>";	
}	
	 
$frm[2].="</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	 </div>
     </fieldset>";
//==================================================	 	 
$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
$hfrm[2]=$_SESSION['lang']['pembayaran'];
	 
drawTab('FRM',$hfrm,$frm,100,'100%');	  
CLOSE_BOX();
echo close_body();
?>
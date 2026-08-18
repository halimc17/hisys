<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/pabrik_permintaan_pembersihantangki.js?ver=3.8'></script>
<?


$frm[0]='';
$frm[1]='';

$optTangki="";   
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch())
{
    $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}	

$optBrg="";
$opttipe=$optBrgSch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optBrgSch.="<option value='40000001'>CRUDE PALM OIL (CPO)</option>";
$optBrgSch.="<option value='40000002'>PALM KERNEL (PK)</option>";

$opttipe.="<option value='Cuci'>Cuci</option>";
$opttipe.="<option value='Return'>Return</option>";

$optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//persetujuan1
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSCUCITANGKI' and level=1 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan2
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSCUCITANGKI' and level=2 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper2.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}

//persetujuan3
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PKSCUCITANGKI' and level=3 and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$whr=" karyawanid='".$bar['karyawanid']."'";
	$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

	$optper3.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
}


#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}
	
?>


<?php
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Storage Cleaning Recognition Request').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Permintaan Berita Acara Pencucian Tanki').'</span>');
}

$frm[0].="<fieldset style=\"width:550px;\" >
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>".$_SESSION['lang']['pabrik']."</td>
                                    <td>:</td>
                                    <td><select id=pabrik onchange=getTangki() style=\"width:150px;\" >".$optOrg."</select></td>
				</tr>
                                <tr>
                                    <td>".$_SESSION['lang']['tangki']."</td>
                                    <td>:</td>
                                    <td><select id=tangki onchange=getBarang() style=\"width:150px;\" >".$optTangki."</select></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['namabarang']."</td>
                                    <td>:</td>
                                    <td><select id=barang style=\"width:150px;\">".$optBrg."</select></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['tipe']."</td>
                                    <td>:</td>
                                    <td><select id=tipe style=\"width:150px;\">".$opttipe."</select></td>
				</tr>
                                <tr>
                                    <td>".$_SESSION['lang']['tanggal']."</td>
                                    <td>:</td>
                                    <td><input type=text class=myinputtext id=tgl name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px; onblur=getSounding() />
                                        <select id=jm>".$jm."</select>:<select id=mn>".$mnt."</select></td>
                                </tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['noberitaacara']."</td>
                                    <td>:</td>
                                    <td><input type=text id=noba size=10 class=myinputtext maxlength=50 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
								</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['stockawal']."</td>
                                    <td>:</td>
                                    <td><input type=text id=sawal size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" disabled /></td>
								</tr>
								<tr>
                                    <td width=100>".$_SESSION['lang']['recyclestock']."</td>
                                    <td>:</td>
                                    <td><input type=text id=jmlRey size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" onblur=itungSisa(); /></td>
								</tr>
								<tr>
                                    <td width=100>Non Oil Solid</td>
                                    <td>:</td>
                                    <td><input type=text id=jmlWaste size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" disabled /></td>
								</tr>
                                <tr>
                                    <td width=100>".$_SESSION['lang']['keterangan']."</td>
                                    <td>:</td>
                                    <td><input type=text id=ket size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
								</tr>
				<tr>
                                    <td></td><td></td>
                                 
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";





					







$frm[0].="
	
	<div id=formpersetujuan ".$display.">
		<fieldset>
		  <legend>".$_SESSION['lang']['approve']."</legend>
		  <table>
		  	".$atasan."
		  	<tr>
				<td>Persetujuan 1</td> 
				<td>:</td>
				<td><select id=persetujuan1 style='width:150px'>".$optper1."</select></td>
			</tr>
		   	<tr>
			    <td>Persetujuan 2</td> 
			    <td> : </td>
				<td><select id=persetujuan2 style='width:150px'>".$optper2."</select></td>
			</tr>
			<tr>	
			    <td>Persetujuan 3</td>
				<td> : </td>
				<td><select id=persetujuan3 style='width:150px'>".$optper3."</select></td>					 
			</tr>
			<tr style='display:none;'>	
			    <td>Direksi</td>
				<td> : </td>
				<td><select id=persetujuan4 style='width:150px'>".$optper4."</select></td>					 
			</tr>
		   </table>
		</fieldset>
	 </div>	 
	 <table>
	 <tr><td style=width:67px><td>
	   <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
	   <button class=mybutton onclick=hapus()>".$_SESSION['lang']['new']."</button>
	   <input type=hidden id=method value='insert'>
	 </table>
	 </fieldset>";
	 




//
$frm[1]="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset><legend>".$_SESSION['lang']['find']."</legend>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=13>
	  <button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset>
	  <fieldset><legend>".$_SESSION['lang']['list']."</legend>
	  <div  style='overflow:auto;max-width:900px'; >
	  <table class=sortable cellspacing=1 border=0 style=width:890px>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['notransaksi']."</td>
	  <td align=center>".$_SESSION['lang']['karyawan']."</td>
	  <td align=center>".$_SESSION['lang']['tanggalsurat']."</td>
	  <td align=center>".$_SESSION['lang']['approval_status']."</td>
	  <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>";
$limit=20;
$page=0;
//========================
//ambil jumlah baris dalam tahun ini

$str="select count(*) as jlhbrs from ".$dbname.".pabrik_pembersihantangki 
        where updateby='".$_SESSION['standard']['userid']."' 
		order by jlhbrs desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$jlhbrs=$bar->jlhbrs;
}		
//==================
		 
  if(isset($_POST['page']))
     {
	 	$page=$_POST['page'];
	    if($page<0)
		  $page=0;
	 }
	 
  
  $offset=$page*$limit;  

  $str="select * from ".$dbname.".pabrik_pembersihantangki 
        where updateby='".$_SESSION['standard']['userid']."' 
		limit ".$offset.",20";	

	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
  $no=$page*$limit;
  while($bar=$res->fetch())
  {
  	$no+=1;

	  $namakaryawan='';
	  $strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->updateby;
	  $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	  $resx->setFetchMode(PDO::FETCH_OBJ);
	  while($barx=$resx->fetch())
	  {
	  	$namakaryawan=$barx->namakaryawan;
	  }
	  $add='';
	  if($bar->statuspersetujuan==0)
	  {
	  	$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
		 &nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kodeorg."','".$bar->kodetangki."','".$bar->kodebarang."','".$nmBrg[$bar->kodebarang]."','".$bar->tipe."','".tanggalnormal(substr($bar->tanggal,0,10))."','".substr($bar->tanggal,11,2)."',
                        '".substr($bar->tanggal,14,2)."','".$bar->jumlah."','".$bar->keterangan."','".$bar->noba."','".$bar->sawal."','".$bar->recycle_jmlh."');\">
         ";
	  }
   if($bar->statuspersetujuan==2)
     $stpersetujuan=$_SESSION['lang']['ditolak'];
   else if($bar->statuspersetujuan==1)
    $stpersetujuan=$_SESSION['lang']['disetujui'];
   else {
    $stpersetujuan=$_SESSION['lang']['wait_approve'];	
	
   }

   if($bar->statushrd==2)
     $sthrd=$_SESSION['lang']['ditolak'];
  else if($bar->statushrd==1)
     $sthrd=$_SESSION['lang']['disetujui'];
  else{
     $sthrd=$_SESSION['lang']['wait_approve'];
	 $sthrd.="<br> &nbsp ".$_SESSION['lang']['ganti'].":<select   style='width:100px;' onchange=ganti(this.options[this.selectedIndex].value,'hrd','".$bar->notransaksi."')>".$optKar2."</select>";
  }


	$jenispersetujuan='PKSCUCITANGKI';
  	$stat = array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'3'=>$_SESSION['lang']['ditolak'] );
  	$strap="select * from ".$dbname.".approval 
        where notransaksi='".$bar->notransaksi."'  and  jenispersetujuan='".$jenispersetujuan."'
		order by level asc";	
	$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
	$resap->setFetchMode(PDO::FETCH_ASSOC);
	while($barap=$resap->fetch())
	{
		$ttl.="Persetujuan ".$barap['level']." : ".$stat[$barap['status']]."\n";
	}
  
  
  
	$frm[1].="<tr class=rowcontent>
	  <td align=center>".$no."</td>
	  <td>".$bar->noba."</td>
	  <td>".$namakaryawan."</td>
	  <td>".tanggalnormal($bar->tanggal)."</td>
	  <td align=center title='".$ttl."'>".$stpersetujuan."</td>	
	  <td align=center>
	     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."','".$bar->jeniskaryawan."',event);\"> 
       ".$add."
	  </td>
	  </tr>";
  }
	$frm[1].="<tr>
		<td colspan=11 align=center>".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
		<br>
		<button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	</tr>";	   
$frm[1].="</tbody>
	   <tfoot>
	   </tfoot>
	   </table></div>
	 </fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
 	 
drawTab('FRM',$hfrm,$frm,100,1200);
CLOSE_BOX();
echo close_body('');
?>
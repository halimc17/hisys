<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/sdm_pertanggungjawabanPJD.js?ver=1.5'></script>

<?
	include('master_mainMenu.php');

	OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pertanggungjawabandinas']).'</span>');

	// $str="select * from ".$dbname.".sdm_pjdinasht where karyawanid=".$_SESSION['standard']['userid']." and lunas=0 and statuspertanggungjawaban=0 and statuspersetujuan=1 and posting=0 and namatamu=''";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_OBJ);
	// $optNo='';
	// $optNo.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// while($bar=$res->fetch())
	// {
		// $optNo.="<option value='".$bar->notransaksi."'>".$bar->notransaksi."</option>";
	// }

	$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas order by keterangan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$optJns='';
	while($bar=$res->fetch())
	{
		$optJns.="<option value='".$bar->id."'>".$bar->keterangan."</option>";
	}

	//query kelompok by
	$optkel='';
	$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		$optkel.="<option value=".$bar['id'].">".$bar['keterangan']."</option>";
	}
	
	/* <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJDOri(event);\"> */
	$title_ = array(
		'rupiah'		=> $_SESSION['lang']['rupiah'],
		'keterangan'	=> $_SESSION['lang']['keterangan'],
		'namakelompok'	=> $_SESSION['lang']['namakelompok'],
		'delete'		=> $_SESSION['lang']['delete']
	);
	$cap_json = json_encode($title_);
	
	$frm[0]="<fieldset>
		<script> var cap_json = $cap_json; </script>
		<legend>".$_SESSION['lang']['form']."</legend>
		<fieldset>
			<table border=0>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
					<td>
						<select id=notransaksi onchange='editPPJD(this.value)'></select>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset>
	    <legend>".$_SESSION['lang']['detail']."</legend>
			<table border=0>
			<table>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td>
						<input type=text class='myinputtext' id='bytgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' />
						<!--s/d -->
						<input type=hidden class='myinputtext' id='bytgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['lokasi']."</td>
					<td>:</td>
					<td><input type=text id=bydet maxlength='50' class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 style='width:250px;' maxlength=80></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakelompok']."</td>
					<td>:</td>
					<td><select id=bykel style=\"width:168px;float:left;margin-right:5px;\">".$optkel."</select><a style=float:left;cursor:pointer; title=".$_SESSION['lang']['tambah']."  onclick=create_new_field('formkelompok',cap_json);><img src=images/plus.png width=17></a></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td>
						<input type=text id=byket  maxlength=50 name=byket placeholder='maksimal karakter 50' class=myinputtext onkeypress=return tanpa_kutip(event); size=40 style=width:250px; maxlength=80>
					</td>
				</tr>
			</table>
			
			<table id=formkelompok rute-count=0 style='min-width:50%;'>
			<!-- Create New Kelompok -->
			</table>
			<br/>
			<button class=mybutton onclick=bysimpan()>".$_SESSION['lang']['save']."</button>
		</fieldset>
		<fieldset>
	    <legend>".$_SESSION['lang']['datatersimpan']."</legend>
		<div style='overflow:auto;height:250px;max-width:900px'; >
			<table class=sortable cellspacing=1 border=0>
				<thead>
				<tr>
					<td align=center rowspan=2>No.</td>
					<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>
					<!--<td align=center rowspan=2>".$_SESSION['lang']['namakelompok']."</td>-->
					<td align=center rowspan=2>".$_SESSION['lang']['lokasi']."</td> 
					<td align=center rowspan=2>Uang Perjalanan Dinas</td> 
					<td align=center rowspan=2>Uang Makan</td> 
					<td align=center colspan=2>Penginapan</td> 
					<td align=center rowspan=2>Transportasi</td> 
					<td align=center rowspan=2>Lain Lain</td> 
					<td align=center  rowspan=2>".$_SESSION['lang']['jumlah']."</td>
					<td align=center rowspan=2>".$_SESSION['lang']['keterangan']."</td> 
					<td align=center rowspan=2>".$_SESSION['lang']['action']."</td>
				</tr>
				<tr>
					<td align=center>Hotel</td> 
					<td align=center>Mess</td> 
				</tr>
				</thead>	
				<tbody id=innercontainer>
					<tr class='rowcontent'><td colspan='12'>".$_SESSION['lang']['dataempty']."</td></tr>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</div></fieldset>
		<button class=mybutton onclick=selesai()>".$_SESSION['lang']['done']."</button>
	</fieldset>";

	/* <button class=mybutton onclick=savePPJD()>".$_SESSION['lang']['save']."</button>
	  <tr>
		    <td>".$_SESSION['lang']['tanggal']."<td>:</td><input type=hidden id=method value=insert></td>
			<td><input type=text size=7 id=tanggal class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
			<td>".$_SESSION['lang']['jenisbiaya']."</td><td>:</td>
		    <td><select id=jenisby>".$optJns."</select></td>
			<td>".$_SESSION['lang']['keterangan']."</td><td>:</td>
			<td><input type=text id=keterangan size=30 maxlength=45 class=myinputtext onkeypress=\"return tanpa_kutip(event);\">
		    </td>
			<td>".$_SESSION['lang']['jumlah']."</td><td>:</td>
			<td><input type=text id=jumlah size=12 maxlength=15 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" onblur=change_number(this)>
			</td>
			<td>
			  <button class=mybutton onclick=bysimpan()>".$_SESSION['lang']['save']."</button>
			</td>
			</tr>
	 
	 */
//=======================================
// $frm[0]="<fieldset>
			// ".$_SESSION['lang']['notransaksi']." : 
			// <select id=notransaksi1>".$optNo."</select><br>
			// <legend>Description of the results of official travel</legend>
			// <textarea id=uraian cols=120 rows=15 onkeypress=\"return tanpa_kutip(event);\" align=left valign=top>
			// Tujuan :
			
			
			// Aktifitas :
			
			
			// Hasil :
			// </textarea><br>
			// <button class=mybutton onclick=simpanUraianPjDinas()>".$_SESSION['lang']['save']."</button>
         // </fieldset>
		 // ";		 
		 
		 
//=====================================
// $frm[1]="";
$frm[1].="<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<fieldset><legend>".$_SESSION['lang']['find']."</legend>
	".$_SESSION['lang']['cari_transaksi']."
	<input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=14>
	<button class=mybutton onclick=cariPJD(0)>".$_SESSION['lang']['find']."</button>
	</fieldset>
	<fieldset>
		<legend>".$_SESSION['lang']['hasil']."</legend>
		<table class=sortable cellspacing=1 border=0 >
			<thead>
			<tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['karyawan']."</td>
				<td align=center>".$_SESSION['lang']['tanggalsurat']."</td>
				<td align=center>".$_SESSION['lang']['tujuan']."</td>
				<td align=center>".$_SESSION['lang']['uangmuka']."</td>
				<td align=center>".$_SESSION['lang']['digunakan']."</td>	  
				<td align=center>".$_SESSION['lang']['approval_status']."</td>	  
				<td align=center>Action</td>
			</tr>
			</thead>
		<tbody id=containerlist>";
		$limit=20;
		$page=0;
		//========================
		//ambil jumlah baris dalam tahun ini
		$notransaksi="";
		if(isset($_POST['tex']))
		{
			$notransaksi.=" and notransaksi like '%".$_POST['tex']."%' ";
		}
		
		$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where karyawanid=".$_SESSION['standard']['userid']." ".$notransaksi." and namatamu='' order by jlhbrs desc";
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
		$str="select * from ".$dbname.".sdm_pjdinasht where karyawanid=".$_SESSION['standard']['userid']."
		".$notransaksi." and namatamu='' order by tanggalbuat desc  limit ".$offset.",20";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=$page*$limit;
		while($bar=$res->fetch())
		{
			$no+=1;

			$namakaryawan='';
			$strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_OBJ);
			while($barx=$resx->fetch())
			{
				$namakaryawan=$barx->namakaryawan;
			}
			
			
			if($bar->statuspertanggungjawaban==2)
				$stpersetujuan=$_SESSION['lang']['ditolak'];
			else if($bar->statuspertanggungjawaban==1)
				$stpersetujuan=$_SESSION['lang']['disetujui'];
			else 
				$stpersetujuan=$_SESSION['lang']['wait_approve'];	  
			
			$str1="select sum(jumlahhrd) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$bar->notransaksi."' and sumber=1 ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			$usage=0;
			while($bar1=$res1->fetch())
			{
				$usage=$bar1->jumlah;
			}
			$add='';
			if($bar->statuspertanggungjawaban == 1){
				$add.="<img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']." (Task Result Description)' onclick=\"previewPJDUraian('".$bar->notransaksi."',event);\">";
			}else{
				$add.="<img src=images/pdf.jpg class=resicon  style='filter: grayscale(100%);' title='".$_SESSION['lang']['pdf']." (Task Result Description)' onclick=\"alert('Belum selesai di Verifikasi');\" >";
			}
			if($bar->posting==0 and $bar->statuspertanggungjawaban == 0){
				$add.="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPPJD('".$bar->notransaksi."');\">";
				$add.=" <img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar->notransaksi."');\">";
			}
			$frm[1].="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$bar->notransaksi."</td>
				<td>".$namakaryawan."</td>
				<td>".tanggalnormal($bar->tanggalbuat)."</td>
				<td>".$bar->tujuan1."</td>
				<td align=right>".number_format($bar->uangmuka,2,'.',',')."</td>
				<td align=right>".number_format($usage,2,'.',',')."</td>
				<td>".$stpersetujuan."</td>
				<td align=center>
					<img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']." (Cost)' onclick=\"previewPJD('".$bar->notransaksi."',event);\"> 
					<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar->notransaksi."');\" >
					
					".$add."
				</td>
			</tr>";
		}
		$frm[1].="<tr>
			<td colspan=11 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
				<br>
				<button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
		
		$frm[1].="</tbody>
		<tfoot>
		</tfoot>
		</table>
		</fieldset></fieldset>"; 
//==================================================	 	 


$hfrm[0]=$_SESSION['lang']['form'];
// $hfrm[0]=$_SESSION['lang']['hasilkerjajumlah'];
$hfrm[1]=$_SESSION['lang']['list'];
	 
drawTab('FRM',$hfrm,$frm,100,900);	  
CLOSE_BOX();
echo close_body();
?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/cekakun.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
include_once('lib/utilities.php');
use Dompdf\Dompdf;

$method     = checkPostGet('method','');
if(count($_POST)>0){	
	$param= $_POST;
}else{
	$param= $_GET;
}

// @$param['tanggal']        =tanggalsystemn($param['tanggal']);
// @$param['tanggaldari']    =tanggalsystemn($param['tanggaldari']);
// @$param['tanggaldaridt']  =tanggalsystemn($param['tanggaldaridt']);
// @$param['tanggaldaridtfix']  =tanggalsystemn($param['tanggaldaridtfix']);
// @$param['tanggalsampai']  =tanggalsystemn($param['tanggalsampai']);
// @$param['tanggalsampaidt']=tanggalsystemn($param['tanggalsampaidt']);
// @$param['tanggalsampaidtfix']=tanggalsystemn($param['tanggalsampaidtfix']);


@$param['volume']=str_replace(",","",$param['volume']);
@$param['batasbawah']=str_replace(",","",$param['batasbawah']);
@$param['batasatas']=str_replace(",","",$param['batasatas']);
@$param['kadaluwarsa']=str_replace(",","",$param['kadaluwarsa']);
@$param['harga']=str_replace(",","",$param['harga']);
@$param['ppn']=str_replace(",","",$param['ppn']);


@$param['batasbawahfix']=str_replace(",","",$param['batasbawahfix']);
@$param['batasatasfix']=str_replace(",","",$param['batasatasfix']);
@$param['fixgrading']=str_replace(",","",$param['fixgrading']);

@$param['batasbawahinsentif']=str_replace(",","",$param['batasbawahinsentif']);
@$param['batasatasinsentif']=str_replace(",","",$param['batasatasinsentif']);
@$param['rpkginsentif']=str_replace(",","",$param['rpkginsentif']);
				
$stylehidden= "style='display:none'"; 
$path      = "fileupload/keu_jurnalmemorial/";
$table     ='pmn_kontrakbeli';
$tabledt   ='pmn_5hargabelitbs'; #pmn_5hargabelitbs
$tabledtfix='pmn_5fixgrading';
$tabledtinsentif='pmn_5insentif';
$tablevw   ='keu_jurnalmemorialdt_vw';

$tab="";

switch ($method) {
	case'datadetail':
		$arrdt="###tanggaldaridt###tanggalsampaidt###harga###kelas###ppn###pph###notransaksidt###iddt###tahuntanam###hargabrondolan";
		$optkelas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".pmn_5kelasbuah where status='A'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optkelas.="<option value='".$bar['kode']."'>".$bar['namakelas']."</option>";
		}
		
        ### TAB HARGA TBS ###

		// echo "<pre>";
		// print_r(trim($param['notransaksi']));
		// echo "</pre>";
		// echo trim($param['notransaksi']);
		// echo '<br><br>';
		$frm[0]=$frm[1]=$frm[2]='';
		$frm[0].="<fieldset>";
		$frm[0].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
		$frm[0].="<table cellspacing=1 border=0>
				<tr>
					<input hidden id=notransaksidt value=".trim($param['notransaksi']).">
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tanggaldaridt readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>&nbsp;s/d
						<input type=text class=myinputtext id=tanggalsampaidt name=tanggalsampai  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>	
					</td>
					
					<td>".$_SESSION['lang']['harga']." (Rp/Kg)</td>
					<td>:</td>		
					<!--<td><input type=text id=harga style=\"width:100px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('harga',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>--> 
                    <td><input type=text id=harga style=\"width:100px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('harga',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
					
				</tr><tr>	
					<td>Kelas Buah</td>
					<td>:</td>		
					<td>
						<select id=kelas  style=\"width:155px;\">'".$optkelas."'</select>
						<img id=kelas onclick=z.elSearch('kelas',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
					</td>
					
					
					<td>".$_SESSION['lang']['ppn']." (%)</td>
					<td>:</td>		
					<td><input type=text id=ppn style=\"width:100px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('ppn',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
					
					<td>".$_SESSION['lang']['pph']." (%)</td>
					<td>:</td>		
					<td><input type=text id=pph style=\"width:100px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('pph',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
				</tr>
				</tr><tr>	
					<td>".$_SESSION['lang']['tahuntanam']."</td>
					<td>:</td>		
					<td><input type=text id=tahuntanam maxlength=4 style=\"width:150px;\" class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
					
				
					
				</tr>
				<tr>
					<td style=width:149px;>Upload CSV</td>
					<td>:</td>
					<td><a href=# onclick=showform();>Show form upload</a></td>

				</tr>
				<tr>
					<td align=center colspan=2></td>
					<td>
						<input hidden type=text id=iddt>
						<input hidden type=text id=methoddt value='savedt'>
						<button class=mybutton onclick=savedt('".$arrdt."')>".$_SESSION['lang']['save']."</button>&nbsp;
						<button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
				<tr hidden>
					<td>".$_SESSION['lang']['harga']." ".$_SESSION['lang']['brondolan']." (Rp/Kg)</td>
					<td>:</td>		
					<td><input type=text id=hargabrondolan style=\"width:100px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('hargabrondolan',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
					
					
				</tr>
			</table></fieldset>";
		$frm[0].="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table cellspacing=1 cellpadding=1 style='width:55%'>
					<tr>
						<td>".$_SESSION['lang']['periode']."</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext id=tgl1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>
							&nbsp;s/d&nbsp;
							<input type=text class=myinputtext id=tgl2 name=tanggalsampai  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>	
						</td>
						<td>Kelas Buah</td>
						<td>:</td>
						<td>
							<select id=klsbuah  style=\"width:155px;\">'".$optkelas."'</select>
							<img id=kelas onclick=z.elSearch('kelas',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
						</td>
						<td>
							<button class=mybutton onclick=loaddatadt('".trim($param['notransaksi'])."',0)>".$_SESSION['lang']['find']."</button>
						</td>
					</tr>
				</table>
				<table cellpading=1 cellspacing=1 border=0 class=sortable style='width:55%'>
				<thead>
					<tr class=rowheader>
						<th rowspan=2 align=center>No</th>
						<th colspan=2 align=center>".$_SESSION['lang']['periode']." </th> 
						<th rowspan=2 align=center>".$_SESSION['lang']['tahuntanam']."<br></th> 
						<th rowspan=2 align=center>Kelas Buah</th>
						<th rowspan=2 align=center>".$_SESSION['lang']['harga']."<br>(Rp/Kg)</th>
						<th rowspan=2 align=center>".$_SESSION['lang']['ppn']."<br>(%)</th> 
						<th rowspan=2 align=center>".$_SESSION['lang']['pph']."<br>(%)</th> 
						
						<th rowspan=2 align=center colspan=2>".$_SESSION['lang']['action']." </th> 
					</tr>  
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['mulai']." </th>
						<th align=center>".$_SESSION['lang']['akhir']." </th>
					</tr>  
				</thead>
			 <tbody id=listdatadt>
				<script>loaddatadt('".trim($param['notransaksi'])."',0)</script>
			</tbody>
			</table>
		</fieldset>";
		/*
		$arrfix="###tanggaldaridtfix###tanggalsampaidtfix###batasbawahfix###batasatasfix###fixgrading###notransaksidt###iddtfix";
		$frm[1].="<fieldset>
				<legend><b>".$_SESSION['lang']['form']."</b></legend>
				<table cellspacing='1' border='0'>
					<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tanggaldaridtfix readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>&nbsp;s/d
						<input type=text class=myinputtext id=tanggalsampaidtfix name=tanggalsampai  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>	
					</td>
				</tr><tr>	
					<td>".$_SESSION['lang']['batasbawah']." (%)</td>
					<td>:</td>		
					<td><input type=text id=batasbawahfix style=\"width:150px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('batasbawahfix',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
				</tr><tr>	
					<td>".$_SESSION['lang']['batasatas']." (%)</td>
					<td>:</td>		
					<td><input type=text id=batasatasfix style=\"width:150px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('batasatasfix',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
					
				</tr><tr>	
					<td>Fix Grading (%)</td>
					<td>:</td>		
					<td><input type=text id=fixgrading style=\"width:150px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('fixgrading',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
				</tr>
				<tr>
					<td align=center colspan=2></td>
					<td>
						<input hidden type=text id=iddtfix>
						<input hidden type=text id=methoddtfix value='savedtfix'>
						<button class=mybutton onclick=savedtfix('".$arrfix."')>".$_SESSION['lang']['save']."</button>&nbsp;
						<button id=batal class=mybutton onclick=canceldtfix()>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
				</table></fieldset>";
			$frm[1].="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr class=rowheader>
						<th rowspan=2 align=center>No</th>
						<th colspan=2 align=center>".$_SESSION['lang']['periode']." </th> 
						<th rowspan=2 align=center>".$_SESSION['lang']['batasbawah']."<br>(%)</th>
						<th rowspan=2 align=center>".$_SESSION['lang']['batasatas']."<br>(%)</th> 
						<th rowspan=2 align=center>Fix Grading<br>(%)</th> 
						<th rowspan=2 align=center colspan=2>".$_SESSION['lang']['action']." </th> 
					</tr>  
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['mulai']." </th>
						<th align=center>".$_SESSION['lang']['akhir']." </th>
					</tr>  
				</thead>
			 <tbody id=listdatadtfix>
			</tbody>
			</table>
		</fieldset>";
		
		$arrinsentif="###tanggaldaridtinsentif###tanggalsampaidtinsentif###batasbawahinsentif###batasatasinsentif###rpkginsentif###notransaksidt###iddtinsentif";
		$frm[2].="<fieldset>
				<legend><b>".$_SESSION['lang']['form']."</b></legend>
				<table cellspacing='1' border='0'>
					<tr>
					<td>".$_SESSION['lang']['periode']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tanggaldaridtinsentif readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>&nbsp;s/d
						<input type=text class=myinputtext id=tanggalsampaidtinsentif name=tanggalsampai  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:62px;/>	
					</td>
				</tr><tr>	
					<td>".$_SESSION['lang']['batasbawah']." (Kg)</td>
					<td>:</td>		
					<td><input type=text id=batasbawahinsentif style=\"width:150px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('batasbawahinsentif',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
				</tr><tr>	
					<td>".$_SESSION['lang']['batasatas']." (Kg)</td>
					<td>:</td>		
					<td><input type=text id=batasatasinsentif style=\"width:150px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('batasatasinsentif',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
					
				</tr><tr>	
					<td>".$_SESSION['lang']['rpkg']."</td>
					<td>:</td>		
					<td><input type=text id=rpkginsentif style=\"width:150px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('rpkginsentif',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
				</tr>
				<tr>
					<td align=center colspan=2></td>
					<td>
						<input hidden type=text id=iddtinsentif>
						<input hidden type=text id=methoddtinsentif value='savedtinsentif'>
						<button class=mybutton onclick=savedtinsentif('".$arrinsentif."')>".$_SESSION['lang']['save']."</button>&nbsp;
						<button id=batal class=mybutton onclick=canceldtinsentif()>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
				</table></fieldset>";
			$frm[2].="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr class=rowheader>
						<th rowspan=2 align=center>No</th>
						<th colspan=2 align=center>".$_SESSION['lang']['periode']." </th> 
						<th rowspan=2 align=center>".$_SESSION['lang']['batasbawah']."<br>(%)</th>
						<th rowspan=2 align=center>".$_SESSION['lang']['batasatas']."<br>(%)</th> 
						<th rowspan=2 align=center>".$_SESSION['lang']['rpkg']."</th> 
						<th rowspan=2 align=center colspan=2>".$_SESSION['lang']['action']." </th> 
					</tr>  
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['mulai']." </th>
						<th align=center>".$_SESSION['lang']['akhir']." </th>
					</tr>  
				</thead>
			 <tbody id=listdatadtinsentif>
			</tbody>
			</table>
		</fieldset>";
		*/
		
		$hfrm[0]=('Harga TBS');
		// $hfrm[1]=('Fix Grading');
		// $hfrm[2]=('Insentif');
		drawTab('FRM',$hfrm,$frm,150,'auto');
	break;

	case 'examplecsv':
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplekontrakjual.csv");
        echo "notransaksi,tanggaldari,tanggalsampai,kodeklsbuah,unit,kodesupplier,ppn,harga,tahuntanam,hargabrondolan\n";
        echo "001/TBS/ASPM/CV/06/2021,2021-04-01,2021-04-30,BB,ASPM,S202005007,0,0,0,0\n";
        echo "001/TBS/RWKM/ex/05/2021,2021-04-01,2021-04-30,BB,RWKM,S202005008,0,0,0,0\n";
        exit();
    break;

	case 'formupload':
        $form = "
        <fieldset>
        <legend id=fieldsetnotransaksi><b>".$notransaksi."</b></legend>
        <table border=0>
			<tr>
				<td style=width:150px;>Format</td>
				<td>:</td>
				<td>Format wajib mengikuti contoh berikut, <a href=pmn_kontrakbeli_slave.php?method=examplecsv target=frame>Click here for example</a></td>
			</tr>
			<tr>
				<td>File</td>
				<td>:</td>
				<td><input name=upload type=file id=upload class=mybutton style=width:160px;></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td><button id=uploadcsv class=mybutton onclick=uploadcsv()>" . $_SESSION['lang']['upload'] . "</button></td>
			</tr>
        </table>
        </fieldset>
        ";

        $form.="<table class='sortable' cellspacing='1' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center>No</th>
				<th colspan=2 align=center>".$_SESSION['lang']['periode']." </th> 
				<th rowspan=2 align=center>".$_SESSION['lang']['tahuntanam']."<br></th> 
				<th rowspan=2 align=center>Kelas Buah</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['harga']."<br>(Rp/Kg)</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['ppn']."<br>(%)</th> 
			</tr>  
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['mulai']." </th>
				<th align=center>".$_SESSION['lang']['akhir']." </th>
			</tr>  
		</thead>";

			$str = "select * from ".$dbname.".".$tabledt."  where notransaksi='".trim($param['notransaksi'])."' order by id desc"; ## YG GW RUBAH
			$res=fetchdata($str);
			if(empty($res))
            {
                $form.="<tr class=rowcontent><td colspan=10 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }else{
				foreach($res as $bar){
					$no++;
					$optkls=makeOption($dbname,'pmn_5kelasbuah','kode,namakelas',"kode='".$bar['kodeklsbuah']."'");
					$form.="<tr class=rowcontent style=height:25px>";
					$form.="<td align=center>".$no."</td>";
					$form.="<td >".tanggalnormal($bar['tanggaldari'])."</td>";
					$form.="<td >".tanggalnormal($bar['tanggalsampai'])."</td>";
					$form.="<td align=center>".$bar['tahuntanam']."</td>";
					$form.="<td align=left>".@$optkls[$bar['kodeklsbuah']]."</td>";
					$form.="<td align=right>".number_format($bar['harga'],2)."</td>";
					// $form.="<td align=right>".number_format($bar['hargabrondolan'],2)."</td>";
					$form.="<td align=right>".number_format($bar['ppn'],2)."</td>";
					
					
					// $form.="<td align=center style=\"width:25px;\"><img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdt('".$bar['id']."');\"></td>";						
					// $form.="<td align=center style=\"width:25px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deletedt('".$bar['id']."','".trim($param['notransaksi'])."');\"></td>";
					
					// $form.="</tr>";
				}
			}
        $form.="</table>";
        echo $form;    
    break;

	case 'uploadcsv':
		$notransaksi     = checkPostGet('notransaksi','');
		// echo"<pre>";
		// print_r($notransaksi);
		// echo"</pre>";
		// exit();
        insertcsv($notransaksi);
    break;

	case'saveht':
		try {
			$owlPDO->beginTransaction();
			if($param['kodeunit']==''){
				throw new PDOException("Pabrik harus diisi.");
			}
			if($param['jenis']==''){
				throw new PDOException("Jenis harus diisi.");
			}
			if($param['kodesupplier']==''){
				throw new PDOException("Vendor harus diisi.");
			}
			if($param['kodebarang']==''){
				throw new PDOException("Produk harus diisi.");
			}
			if($param['tanggal']==''){
				throw new PDOException("Tanggal kontrak harus diisi.");
			}
			/*
			if($param['tanggaldari']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampai']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}
			
			if($param['tanggaldari']>$param['tanggalsampai']){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			if($param['tanggalsampai']<$param['tanggaldari']){
				throw new PDOException("Tanggal sampai tidak boleh lebih kecil dari tanggal dari.");
			}
			*/

			if (substr(tanggalsystemn($param['tanggaldari']),0,7) != substr(tanggalsystemn($param['tanggal']),0,7)) {
				throw new PDOException("Periode Dari Tidak Boleh Berbeda dari Periode Tanggal Kontrak.");
			}

			if (substr(tanggalsystemn($param['tanggalsampai']),0,7) != substr(tanggalsystemn($param['tanggaldari']),0,7)) {
				throw new PDOException("Periode Sampai Tidak Boleh Berbeda dari Periode Dari.");
			}

			// Add Validasi Tanggal Sampai Tidak boleh lebih kecil dari tanggal dari
			if (tanggalsystemn($param['tanggalsampai']) < tanggalsystemn($param['tanggaldari'])) {
				throw new PDOException("Periode Sampai Tidak Boleh Lebih Kecil dari Periode Dari.");
			}

			// exit('warning:'.substr(tanggalsystemn($param['tanggalsampai']),0,7));

			if ($param['batasbawah'] > $param['batasatas']) {
				throw new PDOException("Batas Bawah harus lebih kecil dari Batas Atas.");
			}

			// $arrtgl = explode('-',tanggalsystemn($param['tanggal']));
			// $tahun  = $arrtgl[0];
			// $bulan  = $arrtgl[1];
			// $day    = $arrtgl[2];
			// $periode=$arrtgl[0]."-".$arrtgl[1];			

			$str="select * from ".$dbname.".log_5masterbarang where kodebarang='".$param['kodebarang']."'";
			$res=fetchdata($str);
			$inisialbrg = $res[0]['inisial'];
			if($inisialbrg==''){
				throw new PDOException("Inisial untuk kode produk ".$res[0]['namabarang']." belum ada, silahkan tambahkan melalui : Pengadaan - Setup - Master Barang.");
			}
			
			## GENERATE NO KONTRAK
			$tgl=explode("-",$param['tanggal']);
			$str="select max(notransaksi) as nokontrak from ".$dbname.".pmn_kontrakbeli where unit='".$param['kodeunit']."' and left(tanggal,4)='".$tgl[2]."'";
			$res=fetchdata($str);
			$tmpnoKntak=explode("/",$res[0]['nokontrak']);
			$noKntak=explode("-",$tmpnoKntak[0]);
			if(@intval($tmpnoKntak[0])==0){
				@$nourut=addZero((intval($tmpnoKntak[0])+1),3);
			}else{
				@$nourut=addZero((intval($tmpnoKntak[0])+1),3);
			}
			
			$nokontrak=$nourut."/".$inisialbrg."-IN/".$param['kodeunit']."/".romawi(intval($tgl[1]))."/".substr($tgl[2],2,2);
			$data = array(
				'notransaksi'     =>$nokontrak,
				'unit'            =>$param['kodeunit'],
				'jenis'           =>$param['jenis'],
				'kodesupplier'    =>$param['kodesupplier'],
				'kodebarang'      =>$param['kodebarang'],
				'tanggal'         =>tanggalsystemn($param['tanggal']),
				'tanggaldari'     =>tanggalsystemn($param['tanggaldari']),
				'tanggalsampai'   =>tanggalsystemn($param['tanggalsampai']),
				'volume'          =>$param['volume'],
				'batasbawah'      =>$param['batasbawah'],
				'batasatas'       =>$param['batasatas'],
				'bataskadaluwarsa'=>$param['kadaluwarsa'],
				'reffharga'       =>$param['reffharga'],
				'keterangan'      =>$param['keterangan'],
				'dropship'        =>$param['dropship'],
				'updateby'        =>$_SESSION['standard']['userid'],
				'createby'        =>$_SESSION['standard']['userid'],
				'updatetime'      =>date("Y-m-d H:i:s"),
				'createtime'      =>date("Y-m-d H:i:s"),
				'flagall'         =>$param['kball']

			);
			
			$str = insertQuery($dbname,$table,$data,array_keys($data));

			$owlPDO->exec($str);
			
			echo $nokontrak;
			
			$owlPDO->commit();	 		
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
		#exit("error");
	break;
	case'savedtfix':
		
		try {
			$owlPDO->beginTransaction();
			if($param['tanggaldaridtfix']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampaidtfix']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}

			if (substr(tanggalsystemn($param['tanggalsampaidtfix']),0,7) != substr(tanggalsystemn($param['tanggaldaridtfix']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}

			// exit('warning:'.substr(tanggalsystemn($param['tanggalsampai']),0,7));

			if ($param['batasbawah'] > $param['batasatas']) {
				throw new PDOException("Batas Bawah harus lebih kecil dari Batas Atas.");
			}
			
			if(tanggalsystemn($param['tanggaldaridtfix'])>tanggalsystemn($param['tanggalsampaidtfix'])){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			if(tanggalsystemn($param['tanggalsampaidtfix'])<tanggalsystemn($param['tanggaldaridtfix'])){
				throw new PDOException("Tanggal sampai tidak boleh lebih kecil dari tanggal dari.");
			}

			if($param['batasbawahfix']==''){
				throw new PDOException("Batas bawah harus diisi.");
			}
			if($param['batasatasfix']==''){
				throw new PDOException("Batas atas harus diisi.");
			}
			if($param['fixgrading']==''){
				throw new PDOException("Fix Grading harus diisi.");
			}
			
			if($param['batasbawahfix']>100){
				throw new PDOException("Persentase maksimal hanya 100.");
			}
			if($param['batasatasfix']>100){
				throw new PDOException("Persentase maksimal hanya 100.");
			}
			
			if($param['batasbawahfix']>$param['batasatasfix']){
				throw new PDOException("Batas bawah tidak boleh lebih besar dari batas atas.");
			}
			
			$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($param['notransaksi'])."'");
			@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($param['notransaksi'])."'");
			$data = array(
				'notransaksi'   =>trim($param['notransaksi']),
				'unit'   		=>$optunit[trim($param['notransaksi'])],
				'kodesupplier'	=>$optsup[trim($param['notransaksi'])],
				'tanggaldari'   =>tanggalsystemn($param['tanggaldaridtfix']),
				'tanggalsampai'	=>tanggalsystemn($param['tanggalsampaidtfix']),
				'batasbawah'  	=>$param['batasbawahfix'],
				'batasatas'   	=>$param['batasatasfix'],
				'fixgrading'  	=>$param['fixgrading'],
				'updateby'    	=>$_SESSION['standard']['userid'],
				'createby'    	=>$_SESSION['standard']['userid'],
				'updatetime'  	=>date("Y-m-d H:i:s"),
				'createtime'  	=>date("Y-m-d H:i:s")
			);
			
			$str = insertQuery($dbname,$tabledtfix,$data,array_keys($data));
			// exit("Error:$str");
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	
	case'savedtinsentif':
		
		try {
			$owlPDO->beginTransaction();
			if($param['tanggaldaridtinsentif']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampaidtinsentif']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}
			
			// if($param['tanggaldaridtinsentif']>$param['tanggalsampaidtinsentif']){
			// 	throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			// }
			// if($param['tanggalsampaidtinsentif']<$param['tanggaldaridtinsentif']){
			// 	throw new PDOException("Tanggal sampai tidak boleh lebih kecil dari tanggal dari.");
			// }

			if (substr(tanggalsystemn($param['tanggalsampaidtinsentif']),0,7) != substr(tanggalsystemn($param['tanggaldaridtinsentif']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}

			if(tanggalsystemn($param['tanggaldaridtinsentif'])>tanggalsystemn($param['tanggalsampaidtinsentif'])){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			if(tanggalsystemn($param['tanggalsampaidtinsentif'])<tanggalsystemn($param['tanggaldaridtinsentif'])){
				throw new PDOException("Tanggal sampai tidak boleh lebih kecil dari tanggal dari.");
			}

			if($param['batasbawahinsentif']==''){
				throw new PDOException("Batas bawah harus diisi.");
			}
			if($param['batasatasinsentif']==''){
				throw new PDOException("Batas atas harus diisi.");
			}
			if($param['rpkginsentif']==''){
				throw new PDOException("rp/kg harus diisi.");
			}
			
			if($param['batasbawahfix']>$param['batasatasfix']){
				throw new PDOException("Batas bawah tidak boleh lebih besar dari batas atas.");
			}
			
			
			// if(){
				// throw new PDOException("insentif Grading harus diisi.");
			// }
			
			$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($param['notransaksi'])."'");
			@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($param['notransaksi'])."'");
			$data = array(
				'notransaksi'   =>trim($param['notransaksi']),
				'unit'   		=>$optunit[trim($param['notransaksi'])],
				'kodesupplier'	=>$optsup[trim($param['notransaksi'])],
				'tanggaldari'   =>tanggalsystemn($param['tanggaldaridtinsentif']),
				'tanggalsampai'	=>tanggalsystemn($param['tanggalsampaidtinsentif']),
				'batasbawah'  	=>$param['batasbawahinsentif'],
				'batasatas'   	=>$param['batasatasinsentif'],
				'rpkg'  		=>$param['rpkginsentif'],
				'updateby'    	=>$_SESSION['standard']['userid'],
				'createby'    	=>$_SESSION['standard']['userid'],
				'updatetime'  	=>date("Y-m-d H:i:s"),
				'createtime'  	=>date("Y-m-d H:i:s")
			);
			
			$str = insertQuery($dbname,$tabledtinsentif,$data,array_keys($data));
			// exit("Error:$str");
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	
	case'updatedtfix':
	
		try {
			$owlPDO->beginTransaction();
			if($param['tanggaldaridtfix']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampaidtfix']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}

			if (substr(tanggalsystemn($param['tanggaldaridtfix']),0,7) != substr(tanggalsystemn($param['tanggalsampaidtfix']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}
			
			if(tanggalsystemn($param['tanggaldaridtfix'])>tanggalsystemn($param['tanggalsampaidtfix'])){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			
			if($param['batasbawahfix']==''){
				throw new PDOException("Batas bawah harus diisi.");
			}
			if($param['batasatasfix']==''){
				throw new PDOException("Batas atas harus diisi.");
			}
			
			if($param['batasbawahfix']>$param['batasatasfix']){
				throw new PDOException("Batas bawah tidak boleh melebihi batas atas.");
			}
			
			if($param['fixgrading']==''){
				throw new PDOException("Fix Grading harus diisi.");
			}
			
			if($param['batasbawahfix']>100){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}
			
			if($param['batasatasfix']>100){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}
			
			if($param['fixgrading']>100){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}
			
			
			$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($param['notransaksi'])."'");
			@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($param['notransaksi'])."'");
			$data = array(
				'notransaksi'   =>trim($param['notransaksi']),
				'unit'   		=>$optunit[trim($param['notransaksi'])],
				'kodesupplier'  =>$optsup[trim($param['notransaksi'])],
				'tanggaldari'   =>tanggalsystemn($param['tanggaldaridtfix']),
				'tanggalsampai' =>tanggalsystemn($param['tanggalsampaidtfix']),
				'batasbawah'    =>$param['batasbawahfix'],
				'batasatas'     =>$param['batasatasfix'],
				'fixgrading'    =>$param['fixgrading'],
				'updateby'      =>$_SESSION['standard']['userid'],
				'createby'      =>$_SESSION['standard']['userid'],
				'updatetime'    =>date("Y-m-d H:i:s"),
				'createtime'    =>date("Y-m-d H:i:s")
			);
			$where = "id='".$param['iddtfix']."'";
			
			$str = updateQuery($dbname,$tabledtfix,$data,$where);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	
	case'updatedtinsentif':
	
		try {
			$owlPDO->beginTransaction();
			if($param['tanggaldaridtinsentif']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampaidtinsentif']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}

			if (substr(tanggalsystemn($param['tanggaldaridtinsentif']),0,7) != substr(tanggalsystemn($param['tanggalsampaidtinsentif']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}
			
			if(tanggalsystemn($param['tanggaldaridtinsentif'])>tanggalsystemn($param['tanggalsampaidtinsentif'])){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			
			if($param['batasbawahinsentif']==''){
				throw new PDOException("Batas bawah harus diisi.");
			}
			if($param['batasatasinsentif']==''){
				throw new PDOException("Batas atas harus diisi.");
			}
			if($param['rpkginsentif']==''){
				throw new PDOException("Rp/Kg harus diisi.");
			}
			
			$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($param['notransaksi'])."'");
			@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($param['notransaksi'])."'");
			$data = array(
				'notransaksi'   =>trim($param['notransaksi']),
				'unit'      	=>$optunit[trim($param['notransaksi'])],
				'kodesupplier'	=>$optsup[trim($param['notransaksi'])],
				'tanggaldari'   =>tanggalsystemn($param['tanggaldaridtinsentif']),
				'tanggalsampai'	=>tanggalsystemn($param['tanggalsampaidtinsentif']),
				'batasbawah'  	=>$param['batasbawahinsentif'],
				'batasatas'   	=>$param['batasatasinsentif'],
				'rpkg'  		=>$param['rpkginsentif'],
				'updateby'    	=>$_SESSION['standard']['userid'],
				'createby'    	=>$_SESSION['standard']['userid'],
				'updatetime'  	=>date("Y-m-d H:i:s"),
				'createtime'  	=>date("Y-m-d H:i:s")
			);
			$where = "id='".$param['iddtinsentif']."'";
			
			$str = updateQuery($dbname,$tabledtinsentif,$data,$where);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	case'updatedt':
		try {
			$owlPDO->beginTransaction();
			if($param['tanggaldaridt']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampaidt']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}

			if (substr(tanggalsystemn($param['tanggaldaridt']),0,7) != substr(tanggalsystemn($param['tanggalsampaidt']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}
			
			if(tanggalsystemn($param['tanggaldaridt'])>tanggalsystemn($param['tanggalsampaidt'])){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			
			if($param['harga']==''){
				throw new PDOException("Harga harus diisi.");
			}
			
			if($param['ppn']>'100'){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}

			if($param['pph']>'100'){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}
			
			$str="select count(*) as jumlah from ".$dbname.".".$tabledt." where notransaksi='".trim($param['notransaksi'])."' and tanggaldari='".$param['tanggaldaridt']."' and kodeklsbuah='".$param['kelas']."' and tahuntanam='".$param['tahuntanam']."' ";
			$res=fetchdata($str);
			foreach($res as $bar){
				$datatransaksi=$bar['jumlah'];
			}
			
			if($datatransaksi>0){
				throw new PDOException("<br><b>Sudah ada data untuk</b><br>Notransaksi : ".trim($param['notransaksi'])."<br> Tanggal : ".tanggalnormal($param['tanggaldaridt'])."<br> Kelas buah : ".$param['kelas']."<br> Tahun tanam : ".$param['tahuntanam']." .");
			}
			
			
			$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($param['notransaksi'])."'");
			@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($param['notransaksi'])."'");
			$data = array(
				'notransaksi'  	=>trim($param['notransaksi']),
				'unit'      	=>$optunit[trim($param['notransaksi'])],
				'kodesupplier'  =>$optsup[trim($param['notransaksi'])],
				'tanggaldari'  	=>tanggalsystemn($param['tanggaldaridt']),
				'tanggalsampai'	=>tanggalsystemn($param['tanggalsampaidt']),
				'kodeklsbuah'  	=>$param['kelas'],
				'harga'        	=>$param['harga'],
				'hargabrondolan'=>$param['hargabrondolan'],
				'ppn'          	=>$param['ppn'],
				'pph'          	=>$param['pph'],
				'tahuntanam'   	=>$param['tahuntanam'],
				'updateby'     	=>$_SESSION['standard']['userid'],
				'createby'     	=>$_SESSION['standard']['userid'],
				'updatetime'   	=>date("Y-m-d H:i:s"),
				'createtime'   	=>date("Y-m-d H:i:s")
			);
			$where = "id='".$param['iddt']."'";
			
			$str = updateQuery($dbname,$tabledt,$data,$where);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data updte\n" . addslashes($e->getMessage());
		}
	break;
	case'savedt':
		try {
			$owlPDO->beginTransaction();
			if($param['tanggaldaridt']==''){
				throw new PDOException("Periode dari harus diisi.");
			}
			if($param['tanggalsampaidt']==''){
				throw new PDOException("Periode sampai harus diisi.");
			}

			if (substr(tanggalsystemn($param['tanggaldaridt']),0,7) != substr(tanggalsystemn($param['tanggalsampaidt']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}
			
			if(tanggalsystemn($param['tanggaldaridt'])>tanggalsystemn($param['tanggalsampaidt'])){
				throw new PDOException("Tanggal dari tidak boleh lebih besar dari tanggal sampai.");
			}
			if(tanggalsystemn($param['tanggalsampaidt'])<tanggalsystemn($param['tanggaldaridt'])){
				throw new PDOException("Tanggal sampai tidak boleh lebih kecil dari tanggal dari.");
			}
			
			// if($param['kelas']==''){
				// throw new PDOException("Kelas buah harus diisi.");
			// }
			if($param['harga']==''){
				throw new PDOException("Harga harus diisi.");
			}
			
			if($param['ppn']>'100'){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}

			if($param['pph']>'100'){
				throw new PDOException("Persentase tidak boleh melebihi 100.");
			}

			#= cek data sudah ada atau belum
			$str="select count(*) as jumlah from ".$dbname.".".$tabledt." where notransaksi='".trim($param['notransaksi'])."' and tanggaldari='".$param['tanggaldaridt']."' and kodeklsbuah='".$param['kelas']."' and tahuntanam='".$param['tahuntanam']."' ";
			$res=fetchdata($str);
			foreach($res as $bar){
				$datatransaksi=$bar['jumlah'];
			}


			if($datatransaksi>0){
				throw new PDOException("<br><b>Sudah ada data untuk</b><br>Notransaksi : ".trim($param['notransaksi'])."<br> Tanggal : ".tanggalnormal($param['tanggaldaridt'])."<br> Kelas buah : ".$param['kelas']."<br> Tahun tanam : ".$param['tahuntanam']." .");
			}
			
			// echo "<pre>";
			// print_r(trim($param['notransaksi']));
			// echo "</pre>";
			
			@$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($param['notransaksi'])."'");
			@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($param['notransaksi'])."'");
			// exit("Error".$optunit[trim($param['notransaksi'])]);
			$data = array(
				'notransaksi'  =>trim($param['notransaksi']),
				'unit'      =>@$optunit[trim($param['notransaksi'])],
				'kodesupplier'   =>@$optsup[trim($param['notransaksi'])],
				'tanggaldari'  =>tanggalsystemn($param['tanggaldaridt']),
				'tanggalsampai'=>tanggalsystemn($param['tanggalsampaidt']),
				'kodeklsbuah'  =>$param['kelas'],
				'harga'        =>$param['harga'],
				'ppn'          =>$param['ppn'],
				'pph'          =>$param['pph'],
				'tahuntanam'   =>$param['tahuntanam'],
				'hargabrondolan'   =>$param['hargabrondolan'],
				'updateby'     =>$_SESSION['standard']['userid'],
				'createby'     =>$_SESSION['standard']['userid'],
				'updatetime'   =>date("Y-m-d H:i:s"),
				'createtime'   =>date("Y-m-d H:i:s")
			);
			
			$str = insertQuery($dbname,$tabledt,$data,array_keys($data));
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data insert\n" . addslashes($e->getMessage());
		}
	break;
	
	case'updateht':
		try {
			$owlPDO->beginTransaction();
			if($param['kodeunit']==''){
				throw new PDOException("Pabrik harus diisi.");
			}
			if($param['jenis']==''){
				throw new PDOException("Jenis harus diisi.");
			}
			if($param['kodesupplier']==''){
				throw new PDOException("Vendor harus diisi.");
			}
			if($param['kodebarang']==''){
				throw new PDOException("Produk harus diisi.");
			}
			if($param['tanggal']==''){
				throw new PDOException("Tanggal kontrak harus diisi.");
			}

			if ($param['jenis'] == 'prd') {
				if($param['tanggaldari']==''){
					throw new PDOException("Tanggal dari harus diisi.");
				}
				if($param['tanggalsampai']==''){
					throw new PDOException("Tanggal sampai harus diisi.");
				}
			}

			if (substr(tanggalsystemn($param['tanggaldari']),0,7) != substr(tanggalsystemn($param['tanggal']),0,7)) {
				throw new PDOException("Tanggal dari tidak boleh berbeda periode dari tanggal kontrak.");
			}

			if (substr(tanggalsystemn($param['tanggaldari']),0,7) != substr(tanggalsystemn($param['tanggalsampai']),0,7)) {
				throw new PDOException("Tanggal sampai tidak boleh berbeda periode dari tanggal dari.");
			}

			if (tanggalsystemn($param['tanggalsampai']) < tanggalsystemn($param['tanggaldari'])) {
				throw new PDOException("Tanggal sampai tidak boleh lebih kecil dari tanggal dari.");
			}
			
			$arrtgl = explode('-',tanggalsystemn($param['tanggal']));
			$tahun  = $arrtgl[0];
			$bulan  = $arrtgl[1];
			$day    = $arrtgl[2];
			$periode=$arrtgl[0]."-".$arrtgl[1];
			
			
			$data = array(
				'unit'            =>$param['kodeunit'],
				'jenis'           =>$param['jenis'],
				'kodesupplier'        =>$param['kodesupplier'],
				'kodebarang'      =>$param['kodebarang'],
				'tanggal'         =>tanggalsystemn($param['tanggal']),
				'tanggaldari'     =>tanggalsystemn($param['tanggaldari']),
				'tanggalsampai'   =>tanggalsystemn($param['tanggalsampai']),
				'volume'          =>$param['volume'],
				'batasbawah'      =>$param['batasbawah'],
				'batasatas'       =>$param['batasatas'],
				'bataskadaluwarsa'=>$param['kadaluwarsa'],
				'reffharga'       =>$param['reffharga'],
				'keterangan'      =>$param['keterangan'],
				'dropship'        =>$param['dropship'],
				'updateby'        =>$_SESSION['standard']['userid'],
				'createby'        =>$_SESSION['standard']['userid'],
				'updatetime'      =>date("Y-m-d H:i:s"),
				'createtime'      =>date("Y-m-d H:i:s"),
				'flagall'         =>$param['kball']

			);
			$where = "notransaksi='".trim($param['notransaksi'])."'";
			
			$str = updateQuery($dbname,$table,$data,$where);
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	case'editht':
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".trim($param['notransaksi'])."'";
		$res=fetchdata($str)[0];
		echo 
		$res['notransaksi']."###".
		$res['unit']."###".
		$res['jenis']."###".
		$res['kodesupplier']."###".
		$res['kodebarang']."###".
		tanggalnormal($res['tanggal'])."###".
		tanggalnormal($res['tanggaldari'])."###".
		tanggalnormal($res['tanggalsampai'])."###".
		number_format($res['volume'],2)."###".
		number_format($res['batasbawah'],2)."###".
		number_format($res['batasatas'],2)."###".
		$res['bataskadaluwarsa']."###".
		$res['reffharga']."###".
		$res['keterangan']."###".
		$res['dropship']."###".
		$res['flagall'];
	break;
	
	case'editdt':
		$str = "select * from ".$dbname.".".$tabledt."  where id='".$param['id']."'";
		$res=fetchdata($str)[0];
		echo 
		tanggalnormal($res['tanggaldari'])."###".
		tanggalnormal($res['tanggalsampai'])."###".
		$res['kodeklsbuah']."###".
		number_format($res['harga'],2)."###".
		$res['ppn']."###".
		$res['pph']."###".
		$res['tahuntanam']."###".
		$res['hargabrondolan'];
	break;
	case'editdtfix':
		$str = "select * from ".$dbname.".".$tabledtfix."  where id='".$param['id']."'";
		$res=fetchdata($str)[0];
		echo 
		tanggalnormal($res['tanggaldari'])."###".
		tanggalnormal($res['tanggalsampai'])."###".
		$res['batasbawah']."###".
		$res['batasatas']."###".
		$res['fixgrading'];
	break;
	
	case'editdtinsentif':
		$str = "select * from ".$dbname.".".$tabledtinsentif."  where id='".$param['id']."'";
		$res=fetchdata($str)[0];
		echo 
		tanggalnormal($res['tanggaldari'])."###".
		tanggalnormal($res['tanggalsampai'])."###".
		$res['batasbawah']."###".
		$res['batasatas']."###".
		number_format($res['rpkg'],2);
	break;
	
	
	case'loaddata':
		$where='1=1';
		if($param['tanggal']!=''){
			$where.=" and tanggal = '".tanggalsystemn($param['tanggal'])."'";
		}
		
		if(trim($param['notransaksi'])!=''){
			$where.=" and notransaksi like '%".trim($param['notransaksi'])."%'";
		}
		if($param['kodeunit']!=''){
			$where.=" and unit = '".$param['kodeunit']."'";
		}
		
		if($param['kodesupplier']!=''){
			$where.=" and kodesupplier = '".$param['kodesupplier']."'";
		}
		
		if($param['jenis']!=''){
			$where.=" and jenis = '".$param['jenis']."'";
		}
		if($param['kodebarang']!=''){
			$where.=" and kodebarang = '".$param['kodebarang']."'";
		}
		
		
		$where.= " and unit in (".getOrgDetail(2).")";
		
		$limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay = ($page * $limit);
        $offset = $page * $limit;
		$colspan=23;
		
		$str = "select count(*) as jumrow from ".$dbname.".".$table." ";
		// $str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $res->fetch()) {
            $jlhbrs = $jsl->jumrow;
        }
		
		$arrjenis=array('prd'=>'Periode','vol'=>'Volume');
		
		$no = $maxdisplay;
		// $str = "select * from ".$dbname.".".$table." where ".$where." order by tanggal desc,notransaksi desc limit " . $offset . "," . $limit . " ";
		$str = "select * from ".$dbname.".".$table." order by tanggal desc,notransaksi desc limit " . $offset . "," . $limit . " ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['kodesupplier']."'");
			$arrnpwp=makeOption($dbname,'log_5supnpwp','supplierid,npwp',"supplierid='".$bar['kodesupplier']."'");
			// print_r($optsup);
			$optbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			
			$c="onclick=\"formdetail('".$bar['notransaksi']."','".$bar['flagall']."');\"";
			$tab.="<tr class='rowcontent' style=cursor:pointer; title='Click untuk detail.'>";
			$tab.="<td ".$c." align=center valign=middle>".$no.".</td>";
			$tab.="<td ".$c." valign=top nowrap>".$bar['notransaksi']."</td>";
			$tab.="<td ".$c." valign=top>".$bar['unit']."</td>";
			$tab.="<td ".$c." valign=top>".$arrjenis[$bar['jenis']]."</td>";
			$tab.="<td ".$c." valign=top>".$optsup[$bar['kodesupplier']]." </td>";
			$tab.="<td ".$c." valign=top> ".$arrnpwp[$bar['kodesupplier']]." </td>";
			$tab.="<td ".$c." valign=top>".$optbrg[$bar['kodebarang']]."</td>";
			$tab.="<td ".$c." valign=top style='min-width:70px;text-align:center'>".(tanggalnormal($bar['tanggal'])=='--'?'':tanggalnormal($bar['tanggal']))."</td>";
			$tab.="<td ".$c." valign=top style='min-width:70px;text-align:center'>".(tanggalnormal($bar['tanggaldari'])=='--'?'':tanggalnormal($bar['tanggaldari']))."</td>";
			$tab.="<td ".$c." valign=top style='min-width:70px;text-align:center'>".(tanggalnormal($bar['tanggalsampai'])=='--'?'':tanggalnormal($bar['tanggalsampai']))."</td>";
			// $tab.="<td ".$c." align=right valign=top>".hidezerodecimal($bar['volume'],0)."</td>";
			// $tab.="<td ".$c." align=right valign=top>".hidezerodecimal($bar['batasbawah'],2)."</td>";
			// $tab.="<td ".$c." align=right valign=top>".hidezerodecimal($bar['batasatas'],2)."</td>";
			// $tab.="<td ".$c." align=right valign=top>".hidezerodecimal($bar['bataskadaluwarsa'],0)."</td>";
			// $tab.="<td ".$c." align=left valign=top>".$bar['reffharga']."</td>";
			$tab.="<td ".$c." align=left valign=top>".nl2br($bar['keterangan'])."</td>";
			// if($bar['dropship']=='0'){
				// $tab.="<td ".$c." align=left valign=top><input type=checkbox disabled>&nbsp;Tidak</td>";					
			// }else{
				// $tab.="<td ".$c." align=left valign=top><input type=checkbox checked disabled>&nbsp;Ya</td>";
			// }
			$tab.="<td ".$c." valign=top>".getNamaKaryawan($bar['createby'])."</td>";
			$tab.="<td ".$c." valign=top>".getNamaKaryawan($bar['updateby'])."</td>";
			$tab.="<td ".$c." align=center>".($bar['posting'] == 1 ? '<b>Sudah Posting</b>':'Belum Posting')."</td>";
			$tab.="<td ".$c." align=center>".getNamaKaryawan($bar['postingby'])."</td>";
			$tab.="<td hidden style='text-align:center;vertical-align:middle'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('".$bar['notransaksi']."',event)\">History Approval</label></td>";
			
			if($bar['posting'] == 0 || $bar['posting'] == 3){
				$tab.="<td align=center valign=top style=\"width:25px;\">
					<img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['notransaksi']."');\">
				</td>";
				$tab.="<td align=center valign=top style=\"width:25px;\">
					<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."');\">
				</td>";
				$tab.="<td align=center valign=top style=\"width:25px;\">
					<img src=images/icons/04/16/01.png class=zImgBtn  title='Posting' caption='Edit' onclick=\"postinght('".$bar['notransaksi']."');\">
					<img hidden src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`".$bar['notransaksi']."`)'>
				</td>";
			} else if ($bar['posting'] == 9){
				$tab.="<td ".$c."></td>";
				$tab.="<td ".$c."></td>";
				$tab.="<td align=center ".$c."><img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'></td>";
			} else if ($bar['posting'] == 2){
				$tab.="<td ".$c."></td>";
				$tab.="<td ".$c."></td>";
				$tab.="<td align=center ".$c."><img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'></td>";
			} else {
				$tab.="<td ".$c."></td>";
				$tab.="<td ".$c."></td>";
				$tab.="<td align=center ".$c."><img src='images/icons/04/16/02.png' class='zImgBtn' height='30' title='Approved'></td>";
			}
			
			$tab.="</tr>";
            // $no += 1;
        }

		## PAGING
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		
        echo $tab."####".$footd;
	break;
	
	case'loaddatadt':
		$where = '';
		if($param['tgl1'] != '' && $param['tgl2'] != ''){
			$where .= " AND (tanggaldari >= '".tanggalsystemn($param['tgl1'])."' AND tanggalsampai <= '".tanggalsystemn($param['tgl2'])."')";
		}
		if($param['klsbuah'] != ''){
			$where .= " AND kodeklsbuah = '".$param['klsbuah']."'";
		}

		$limit = 10;
        $page = 1;
        $p = new Paging;
		
		if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 1)
                $page = 1;
        }
		
		$maxdisplay = ($page * $limit);
        $offset = $p->cariPosisi($limit,$page);
		$str = "select count(*) as jumrow from ".$dbname.".".$tabledt." where notransaksi='".trim($param['notransaksi'])."' ".$where;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $res->fetch()) {
            $jlhbrs = $jsl->jumrow;
        }
        $jml = $p->jumlahHalaman($jlhbrs,$limit);

		$no = $offset+1;
		$str = "select * from ".$dbname.".".$tabledt."  where notransaksi='".trim($param['notransaksi'])."' ".$where." order by id desc limit ".$offset.",".$limit;
		$res=fetchdata($str);
		foreach($res as $bar){
			$optkls=makeOption($dbname,'pmn_5kelasbuah','kode,namakelas',"kode='".$bar['kodeklsbuah']."'");
			if ($bar['kodeklsbuah'] == 'S'){
				$klsbuah = 'Seluruh Buah';
			} else {
				$klsbuah = $optkls[$bar['kodeklsbuah']];
			}
			// $no++;
			$tab.="<tr class=rowcontent style=height:25px>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td >".tanggalnormal($bar['tanggaldari'])."</td>";
			$tab.="<td >".tanggalnormal($bar['tanggalsampai'])."</td>";
			$tab.="<td align=center>".$bar['tahuntanam']."</td>";
			$tab.="<td align=left>".$klsbuah."</td>";
			$tab.="<td align=right>".number_format($bar['harga'],2)."</td>";
			// $tab.="<td align=right>".number_format($bar['hargabrondolan'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['ppn'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['pph'],2)."</td>";
			
			
			$tab.="<td align=center style=\"width:25px;\"><img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdt('".$bar['id']."');\"></td>";						
			$tab.="<td align=center style=\"width:25px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deletedt('".$bar['id']."','".trim($param['notransaksi'])."');\"></td>";
			
            $tab.="</tr>";
            $no += 1;
        }

        $tab .= "<tr class=rowheader>
          			<td colspan=20 align=center><br>".($offset+1)." to ".($page*$limit)." of ". $jlhbrs."<br />";
		        	$buttonaction = array(
			            'first' =>  "onclick=loaddatadt('".trim($param['notransaksi'])."',1)",
			            'prev'  =>  "onclick=loaddatadt('".trim($param['notransaksi'])."','".($page-1)."')",
			            'next'  =>  "onclick=loaddatadt('".trim($param['notransaksi'])."','".($page+1)."')",
			            'last'  =>  "onclick=loaddatadt('".trim($param['notransaksi'])."','".($jml)."')",
			            'pages' =>  "id='pages' name='pages' onchange=loaddatadt('".trim($param['notransaksi'])."',this.value)"
			        );
        $tab .= $p->navHalaman($page,$jml,$buttonaction);
        $tab .= "</td></tr>";

		echo $tab;
	break;
	case'loaddatadtfix':
		$str = "select * from ".$dbname.".".$tabledtfix."  where notransaksi='".trim($param['notransaksi'])."' order by id desc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent style=height:25px>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td >".tanggalnormal($bar['tanggaldari'])."</td>";
			$tab.="<td >".tanggalnormal($bar['tanggalsampai'])."</td>";
			$tab.="<td align=right>".number_format($bar['batasbawah'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['batasatas'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['fixgrading'],2)."</td>";
			
			$tab.="<td align=center style=\"width:25px;\"><img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdtfix('".$bar['id']."');\"></td>";						
			$tab.="<td align=center style=\"width:25px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deletedtfix('".$bar['id']."','".trim($param['notransaksi'])."');\"></td>";
			
            $tab.="</tr>";
        }
		echo $tab;
	break;
	
	case'loaddatadtinsentif':
		$str = "select * from ".$dbname.".".$tabledtinsentif."  where notransaksi='".trim($param['notransaksi'])."' order by id desc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent style=height:25px>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td >".tanggalnormal($bar['tanggaldari'])."</td>";
			$tab.="<td >".tanggalnormal($bar['tanggalsampai'])."</td>";
			$tab.="<td align=right>".number_format($bar['batasbawah'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['batasatas'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['rpkg'],2)."</td>";
			
			$tab.="<td align=center style=\"width:25px;\"><img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdtinsentif('".$bar['id']."');\"></td>";						
			$tab.="<td align=center style=\"width:25px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deletedtinsentif('".$bar['id']."','".trim($param['notransaksi'])."');\"></td>";
			
            $tab.="</tr>";
        }
		echo $tab;
	break;
	
	case'deleteht':
		$str = "delete from ".$dbname.".".$table." where notransaksi='".trim($param['notransaksi'])."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
	case'postinght':
		$str = "update ".$dbname.".".$table." set posting='1', postingby='".$_SESSION['standard']['userid']."', postingtime='".date('Y-m-d H:i:s')."' where notransaksi='".trim($param['notransaksi'])."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;

	//Umar
	case 'form_ajukan':
		$query  = "SELECT unit FROM $dbname.pmn_kontrakbeli WHERE notransaksi = '".trim($param['notransaksi'])."'";
		$result = fetchData($query, 'OBJECT');
		$unit   = $result[0]->unit;

        $opt    = array();
        $query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = 'KTRKBELI' AND kodeunit = '$unit' ORDER BY level";
        $result = fetchData($query, 'OBJECT');        
        foreach ($result as $key => $value) {
            $opt['approver'][$value->level][$value->karyawanid] = "<option value='".$value->karyawanid."'>".$utilities['worker']['Name'][$value->karyawanid]."</option>";
            $opt['level'][$value->level] = $value->level;
        }

        $jumlahlevel = count($opt["level"]);
        $stream .= "<input type='hidden' id='notransaksi_ajukan' value='".trim($param['notransaksi'])."'/>";
        $stream .= "<input type='hidden' id='jlh' value='".$jumlahlevel."'/>";
        $optShow = "";
        foreach ($opt['approver'][1] as $key => $value) {
            $optShow .= $value;
        }

        $stream .= "<tr class='rowcontent'>";
            $stream .= "<td> Approval ke - 1</td>";
            $stream .= "<td style='width:5px'> : </td>";
            $stream .= "<td>";
                $stream .= "<select id='kepada1' style='width:99%'>".$optShow."</select>";
            $strean .= "</td>";
        $stream .= "</tr>";

        $stream .= "<tr class='rowcontent'>";
            $stream .= "<td></td>";
            $stream .= "<td></td>";
            $stream .= "<td>";
                $stream .= "<button id='tomboldetail' class='mybutton' onclick='ajukan()'>" . $_SESSION['lang']['diajukan'] . "</button>";
            $strean .= "</td>";
        $stream .= "</tr>";

        echo $stream;
    break;

    case 'ajukan':
        for ($i = 1; $i <= $param['jlh'] ; $i++) { 
            $per['persetujuan'.$i] = checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or trim($param['notransaksi']) == ''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }

        $query = "UPDATE $dbname.pmn_kontrakbeli SET posting = '9' WHERE notransaksi = '".trim($param['notransaksi'])."'";
        
        try {
            $owlPDO->exec($query);

			$query  = "SELECT unit FROM $dbname.pmn_kontrakbeli WHERE notransaksi = '".trim($param['notransaksi'])."'";
			$result = fetchData($query, 'OBJECT');
			$unit   = $result[0]->unit;

            $jenispersetujuan = 'KTRKBELI';
            for($i = 1; $i <= $param['jlh']; $i++){
                $query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = '$jenispersetujuan' AND level = '$i' AND kodeunit = '$unit'";
                $result = fetchData($query, 'OBJECT');
                $tipeapp            = $result[0]->tipe;
                $departemenapp      = $result[0]->departemen;
                $tipekaryawanapp    = $result[0]->tipekaryawan;
                $jabatanapp         = $result[0]->jabatan;

                if ($tipeapp == 1) {
                    if ($departemenapp != "") {
                        $query = "SELECT * FROM $dbname.datakaryawan WHERE bagian = '".$departemenapp."'";
                    }
                    
                    if ($tipekaryawanapp != "") {
                        $query = "SELECT * FROM $dbname.datakaryawan WHERE tipekaryawan = '".$tipekaryawanapp."'";
                    }
                    
                    if ($jabatanapp != "") {
                        $query = "SELECT * FROM $dbname.datakaryawan WHERE kodejabatan = '".$jabatanapp."'";
                    }
                    
                    $result = fetchData($query, 'OBJECT');
                    foreach($result as $key => $value){
                        $query = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".trim($param['notransaksi'])."', '".$jenispersetujuan."', '".$i."', '".$valx['karyawanid']."', '0')";

                        $owlPDO->exec($query);
                    }

                    break;
                } else {
                    if($per['persetujuan'.$i] != ''){
                        $query  = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".trim($param['notransaksi'])."', '".$jenispersetujuan."', '".$i."', '".$per['persetujuan'.$i]."', '0')";
                    }
                }

                try {
                    $owlPDO->exec($query);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
	//End Umar
	
	case'deletedt':
		$str = "delete from ".$dbname.".".$tabledt." where id='".$param['id']."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	case'deletedtfix':
		$str = "delete from ".$dbname.".".$tabledtfix." where id='".$param['id']."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	case'deletedtinsentif':
		$str = "delete from ".$dbname.".".$tabledtinsentif." where id='".$param['id']."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
    default:
	// break;
}
function insertcsv($notransaksi){
    global $dbname;
    global $owlPDO;
    $pemisah=',';
    $path='tempExcel';
	

    $dir=$path;
    $ext=explode('.', basename( $_FILES['file']['name']));
    $ext=$ext[count($ext)-1];
    $ext=strtolower($ext);
    
    if($ext=='csv'){
        $path = $dir."/".date('ymd').".".$ext;
        unlink($path);
        try{
            if(move_uploaded_file($_FILES['file']['tmp_name'], $path)){
                $x=readCSV($path,$pemisah);
                $jmlhRow=count($x);
                for($row=1;$row<$jmlhRow;$row++){

					// echo"<pre>";
					// print_r(trim($x[$row][0]));
					// echo"</pre>";
					// exit();012/FB/RWKM/SC/I/2022 012/FB/RWKM/SC/I/2022
					//echo $x[$row][0];
                    if (trim($x[$row][0]) != $notransaksi) {
                        throw new PDOException("Nokontrak tidak sesuai dengan transaksi!!");
                    }

                    // if (trim($x[$row][5]) != $nodo) {
                    //     throw new PDOException("Nodo tidak sesuai dengan transaksi!!");
                    // }
					@$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($x[$row][0])."'");
					@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($x[$row][0])."'");
					//print_r($optsup);
					// exit("Error".$optunit[trim($param['notransaksi'])]);
					// $data = array(
					// 	'notransaksi'  =>trim($x[$row][0]),
					// 	'tanggaldari'  =>tanggalsystemn(trim($x[$row][1])),
					// 	'tanggalsampai'=>tanggalsystemn(trim($x[$row][2])),
					// 	'kodeklsbuah'  =>trim($x[$row][3]),
					// 	'unit'      =>@$optunit[trim($x[$row][4])],
					// 	'kodesupplier'   =>@$optsup[trim($x[$row][5])],
					// 	'ppn'          =>trim($x[$row][6]),
					// 	'harga'        =>trim($x[$row][7]),
					// 	'tahuntanam'   =>trim($x[$row][8]),
					// 	'hargabrondolan'   =>trim($x[$row][9]),
					// 	'createby'     =>$_SESSION['standard']['userid'],
					// 	'createtime'   =>date("Y-m-d H:i:s"),
					// 	'updateby'     =>$_SESSION['standard']['userid'],
					// 	'updatetime'   =>date("Y-m-d H:i:s")
					// );
					
					// $str = insertQuery($dbname,'pmn_5hargabelitbs',$data,array_keys($data));
					// $owlPDO->exec($str);
					
					// $owlPDO->commit();

					$del = "DELETE FROM ".$dbname.".pmn_5hargabelitbs 
							WHERE notransaksi='".trim($x[$row][0])."' AND tanggaldari='".tanggalsystemn(trim($x[$row][1]))."'
							AND tanggalsampai='".tanggalsystemn(trim($x[$row][2]))."' AND unit='".@$optunit[trim($x[$row][0])]."'
							AND kodesupplier='".@$optsup[trim($x[$row][0])]."' AND tahuntanam='".trim($x[$row][8])."'";
					$owlPDO->exec($del);

                    $ha = "insert into ".$dbname.".pmn_5hargabelitbs 
                    (`notransaksi`,
					`tanggaldari`,
					`tanggalsampai`,
					`kodeklsbuah`,
					`unit`,
					`kodesupplier`,
					`ppn`,
					`harga`,
					`tahuntanam`,
					`hargabrondolan`,
					`createby`,
					`createtime`,
					`updateby`,
					`updatetime`) VALUES
                    ('".trim($x[$row][0])."','".tanggalsystemn(trim($x[$row][1]))."','".tanggalsystemn(trim($x[$row][2]))."','".trim($x[$row][3])."','".@$optunit[trim($x[$row][0])]."','".@$optsup[trim($x[$row][0])]."','".trim($x[$row][6])."','".trim($x[$row][7])."','".trim($x[$row][8])."','".trim($x[$row][9])."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
                    //echo $ha;
                    //exit('Error');
                    try{
                      $owlPDO->exec($ha);      
                    }
                    catch (PDOException $e)
                    {
                      print " Gagal  !: " . $e->getMessage() . "<br/>";
                      die();
                    }
                } 
            }
        }catch(Exception $e){
            echo " Gagal, " . addslashes($e->getMessage());
        }
    }else{
   		exit("Error : Mohon upload file tipe CSV");
        echo " Gagal, " . addslashes($e->getMessage());         
    }

}
?>

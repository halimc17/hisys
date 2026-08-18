<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/pmn_sales.js?v=<?= time(); ?>'></script>
<script>
	getSelect2();
</script>
<!--deklarasi untuk option-->
<?php
$optCust=$optBrg=$opttipe=$optppn=$optKurs=$optmillcode=$optsatbrg=$optPosisiCtr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt=$optunit=$optNoref=$optTtdjual=$opttppembayaran=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $val){
	$optPt.="<option value=".$val['kodeorganisasi'].">".$val['namaorganisasi']."</option>";
}

$str="select kodecustomer,namacustomer  from ".$dbname.".pmn_4customer order by namacustomer";
$res=fetchdata($str);
foreach($res as $val){
	$optCust.="<option value=".$val['kodecustomer'].">".$val['namacustomer']."</option>";
}

$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $val){
	$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." ".$val['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".setup_matauang";
$res=fetchdata($str);
foreach($res as $val){
	$optKurs.="<option value='".$val['kode']."'>".$val['matauang']."</option>";
}

#ambil franco
$optByrke=$optTermin=$optFranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select distinct id_franco,franco_name from ".$dbname.".pmn_5franco order by franco_name asc";
$res=fetchdata($str);
foreach($res as $val){
	$optFranco.="<option value='".$val['id_franco']."'>".$val['franco_name']."</option>";
}

#termin pembayaran
$str="select distinct kode from ".$dbname.".pmn_5terminbayar order by kode asc";
$res=fetchdata($str);
foreach($res as $val){
	$optTermin.="<option value='".$val['kode']."'>".$val['kode']."</option>";
}

$arrStatPPn=array("0"=>"Exclude","1"=>"Include");
$optSat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrStatPPn as $row=>$lstNm){
	$optSat.="<option value='".$row."'>".$lstNm."</option>";
}

$str="select * from ".$dbname.".keu_5akunbank order by namabank";
$res=fetchdata($str);
foreach($res as $val){
	$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$val['namabank']."'");
	$optByrke.="<option value='".$val['noakun']."'>".$val['pemilik'].":".$optNamaBank[$val['namabank']]." ".$val['rekening']."</option>";
}

$optKarid = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

$str="select * from ".$dbname.".pmn_5ttd";
$res=fetchdata($str);
foreach($res as $val){
	$optTtdjual.="<option value='".$val['nama']."'>".$optKarid[$val['nama']]."</option>";
}

## LIST POSISI Kontrak
$str = "select * from ".$dbname.".pmn_5lokasikontrak where status='1'";
$res=fetchData($str);
foreach($res as $key=>$val){
	$optPosisiCtr.="<option value='".$val['id']."'>".$val['inisial']."-".$val['lokasi']."</option>";
}

## LIST DAERAH Kontrak
$str = "select * from ".$dbname.".pmn_5daerahkontrak where status='1'";
$opttermbyr=$optDaerahCtr = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=fetchData($str);
foreach($res as $key=>$val){
	$optDaerahCtr.="<option value='".$val['id']."'>".$val['lokasi']."</option>";
}

// $arrtermbyr=array("PM"=>"Pengiriman","BA"=>"Berita Acara Serah Terima"); 
$arrtermbyr=array("BA"=>"Berita Acara Serah Terima"); 
foreach($arrtermbyr as $kei=>$fal){
    $opttermbyr.="<option value='".$kei."'>".$fal."</option>";
} 

$tppembayaran=array("FOB"=>"FOB","CIF"=>"CIF","FRANCO"=>"FRANCO","LOCO"=>"LOCO"); 
foreach($tppembayaran as $val){
    $opttppembayaran.="<option value='".$val."'>".$val."</option>";
}   

$tppembayaran=array("JUAL"=>"JUAL"); 
foreach($tppembayaran as $val){
    $opttipejualbeli.="<option selected value='".$val."'>".$val."</option>";
}   

$str="select * from ".$dbname.".organisasi where tipe='PABRIK'";
$res=fetchdata($str);
foreach($res as $val){
    $optmillcode.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
}

$arrgetenum		= getEnum($dbname,'pmn_kontrakjualv2','tipekontrak');
foreach ($arrgetenum as $hasil) {
	$opttipe   .= "<option value='".$hasil."'>".$hasil."</option>";
}

echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pmn_sales').'</span>');
echo"<table>
	<tr valign=middle>
		<td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
		</td>
		<td>
		<fieldset>
			<legend>".$_SESSION['lang']['find']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['NoKontrak']."</td>
					<td>:</td>		
					<td>
						<input type=text id=nokontraksch size=50 class=myinputtext style=\"width:150px;\">
					</td>
				
					<td style='padding-left:10px'>".$_SESSION['lang']['nmcust']."</td>
					<td> : </td>
					<td><select id=kodecustomersch name=kodecustomersch style=\"width:155px;\">".$optCust."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['pt']."</td>
					<td>:</td>		
					<td><select  name=kodeptsch id=kodeptsch style='width:154px'>".$optPt."</select></td>
					
					<td style='padding-left:10px'>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
						s/d
						<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>			
					</td>
				</tr>
				<tr>
					<td colspan=3>
						<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
		</fieldset>
		</td>
	</tr>
</table> "; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
echo"<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
echo "<table cellpadding=2 cellspacing=1 border=0 class=sortable width=100%>
	<thead>
	<tr class=rowheader>
		<th align=center>".$_SESSION['lang']['nourut']."</th>
		<th align=center>".$_SESSION['lang']['NoKontrak']."</th>
		<th align=center>".$_SESSION['lang']['NoKontrak']." Payung</th>
		<th align=center>".$_SESSION['lang']['nm_perusahaan']."</th>
		<th align=center>".$_SESSION['lang']['nmcust']."</th>
		<th align=center>".$_SESSION['lang']['tglKontrak']."</th>
		<th align=center>".$_SESSION['lang']['produk']."</th>
		<th align=center>Tanggal Kirim</th>
		<th align=center>Term Bayar</th>
		<th align=center>".$_SESSION['lang']['tipetransaksi']."</th>
		<th align=center>".$_SESSION['lang']['status']."</th>
		<th align=center>".$_SESSION['lang']['updateby']."</th>
		<th align=center>".$_SESSION['lang']['status']." Approval</th>
		<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
	</tr>  
	</thead>
	<tbody id=contain> 
		<script>loaddata(0)</script>
	</tbody>
	<tfoot id=footData></tfoot>
</table>";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->
echo "<div id=header style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span>');
echo "<fieldset>
          <legend>".$_SESSION['lang']['form']." </legend>
          <fieldset>
            <legend>".$_SESSION['lang']['header']." & ".$_SESSION['lang']['custInformation']."</legend>
            <table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['NoKontrak']."</td>
				<td> : </td>
				<td>
					<input type=text class=myinputtext id=noKtrk name=noKtrk maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\" disabled />
				</td>
				
				<td>".$_SESSION['lang']['pt']."</td>
				<td><span style='color:blue;font-size:10px;' >(1)</span> : </td>
				<td><select style=\"width:160px;\" id=kdPt name=kdPt onchange='getRek()'>".$optPt."</select></td>
				
				<td>".$_SESSION['lang']['pabrik']."</td>
				<td><span style='color:blue;font-size:10px;' >(2)</span> : </td>
				<td>
					<select style=\"width: 120px;\" name=millcode id=millcode >".$optmillcode."</select>
				</td> 
            </tr>
			
            <tr>
				<td>".$_SESSION['lang']['nmcust']."</td>
				<td><span style='color:blue;font-size:10px;' >(3)</span> : </td>
				<td><select class=select2 id=custId name=custId style=\"width:200px;\">".$optCust."</select></td>

				<td>".$_SESSION['lang']['tglKontrak']."</td>
				<td><span style='color:blue;font-size:10px;' >(4)</span> : </td>
				<td><input type=text style=\"width: 158px;text-align:center\" id=tlgKntrk size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly/></td>
				
				<td hidden>Tipe Kontrak</td>
				<td hidden><span style='color:blue;font-size:10px;' >(5)</span>:</td>
				<td hidden><select id=tipekontrak style='width:120px;' onchange=\"getDataCust(0)\">".$opttipe."</select></td>
            </tr>
           
			<tr> 	 
				<td hidden>".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['external']."</td>
				<td hidden>:</td>
				<td hidden><input type=text class=myinputtext id=noext name=noext maxlength=30 onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\" /></td>
				
				<td>Berikat</td>
				<td><span style='color:blue;font-size:10px;' >(5)</span> : </td>
				<td><input type=checkbox id=berikat >".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."&nbsp;&nbsp; % PPN &nbsp;<input type=text class=myinputtextnumber  name=persenppn id=persenppn maxlength=3 onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('persenppn',2);\"   style=\"width:20px;\" /></td>
				
				<td hidden>No Kontrak Payung</td>
				<td hidden><span style='color:blue;font-size:10px;' >(ww)</span> :</td>
				<td hidden>
					<select id=nokontrakpayung style='width:160px;' onchange='getdetail(this.value)'></select>
				</td>
				
				<td>Tempat Pembuatan Kontrak</td><td><span style='color:blue;font-size:10px;' >(6)</span> : </td>
				<td><select style=\"width:160px;\" id=daerahctr name=daerahctr >".$optDaerahCtr."</select></td>
			</tr>
			
			
			<tr hidden>
				<td>".$_SESSION['lang']['noreferensi']."</td>
				<td> : </td>
				<td style='padding-right:10px'>
					<input type=text class=myinputtext id=noreferensi name=noreferensi maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\" disabled />
					<img src=images/zoom.png title='".$_SESSION['lang']['find']."' class=resicon onclick=carinorefrensi('".$_SESSION['lang']['find']."',event)>
				</td>
				
				<td>Contact Person :</td>
				<td><select  id=nmPerson style=\"width:150px;\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
				<td>".$_SESSION['lang']['nokontrakinduk']."</td><td style='display:none'><select style=\"width:150px;\" id='kntrkRef'>".$optNoref."</select></td>
				
			
				 <td>Lokasi (No. Kontrak)</td>
				 <td> : </td>
				 <td><select style=\"width:100px;\" id=posisictr name=posisictr >".$optPosisiCtr."</select></td>
			</tr>
			
			
			
			</table>
          </fieldset>
		  <br/>
          <fieldset>
			<legend>".$_SESSION['lang']['orderInfor']."</legend>
				<table cellspacing=1 border=0>
				<thead>
				<tr>
					<td colspan=7>".$_SESSION['lang']['goodsDesc']."</td>
				</tr>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>".$_SESSION['lang']['satuan']."</td>
					<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
					<td align=center>".$_SESSION['lang']['matauang']."</td>
					<td align=center>".$_SESSION['lang']['jmlhBrg']."</td>
					<td align=center>PPN</td>
					<td style=\"width:350px;\">".$_SESSION['lang']['terbilang']."</td>
				</tr>
				</thead>
				<tbody>
					<td><select id=kdBrg name=kdBrg onchange=\"getSatuan(0,0,0)\" style=\"width:150px;\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
					<td><select id=stn name=stn style=\"width:50px;\"><option value=''></option></select></td>
					<td><input type=text class=myinputtextnumber  name=HrgStn id=HrgStn onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('HrgStn',2);\"  onblur=\"hitungHarga()\" style=\"width:70px;\" /></td>
					<td><select id=kurs name=kurs style=\"width:80px;\">".$optKurs."</select></td>
					<td><input type=text class=myinputtextnumber name=jmlh id=jmlh onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" onkeyup=\"z.numberFormat('jmlh',2);getBerat();\"  onblur=\"hitungHarga()\" />
					<input id=tmpHarga type=hidden value=0>
					</td>
					<td><select id=ppnId name=ppnId style=\"width:80px;\">".$optSat."</select></td>
					<td width:350><span id=tBlg></span></td>
				</tbody>
				</table><br />
				
		<table cellspacing=1 border=0>
		<td valign=top>
		
			<table cellspacing=1 border=0>
				<thead>
					<tr>
					<td colspan=2>".$_SESSION['lang']['penyerahan']."</td>
					</tr>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['tgl_kirim']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td> <input type=text id=tglKrm0 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly> s.d.<input type=text id=tglSd0 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
						<td><input type=text class=myinputtextnumber name=jmlh0 id=jmlh0 style=\"width:80px;\" onkeypress=\"return angka_doang(event);\"  /></td></tr>
					<tr>
						<td> <input type=text id=tglKrm1 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly> s.d.<input type=text id=tglSd1 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
						<td><input type=text class=myinputtextnumber name=jmlh1 id=jmlh1 style=\"width:80px;\" onkeypress=\"return angka_doang(event);\"   /></td></tr>
					<tr>
						<td> <input type=text id=tglKrm2 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly> s.d.<input type=text id=tglSd2 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
						<td><input type=text class=myinputtextnumber name=jmlh2 id=jmlh2 style=\"width:80px;\" onkeypress=\"return angka_doang(event);\"    /></td></tr>
					<tr>
						<td> <input type=text id=tglKrm3 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly> s.d.<input type=text id=tglSd3 size=7 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly></td>
						<td><input type=text class=myinputtextnumber name=jmlh3 id=jmlh3 style=\"width:80px;\" onkeypress=\"return angka_doang(event);\"  /></td>
					</tr>

                        </tbody>
			</table>
        <td>&nbsp;<td valign=top>            
				<table cellspacing=1 border=0>
					<thead>
                        
                        <td colspan=6 style=\"width:340px;\">".$_SESSION['lang']['kualitas']."</td>
                        </tr></thead>
                        <tbody>
                        <tr>
                        <td>".$_SESSION['lang']['tempatpenyerahan']."</td><td>:</td><td colspan=5><select name=tmbngn id=tmbngn style=\"width: 230px;\" >".$optFranco."</select></td></tr>
                        <tr hidden><td>FFA (%)</td><td>:</td><td><input class=myinputtextnumber id=ffa style=\"width: 60px;\" onkeypress='return angka_doang(event)' /></td>
                        <td width:100px>DOBI</td><td>:</td><td><input class=myinputtextnumber id=dobi style=\"width: 70px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr hidden><td>M & I (%)</td><td>:</td><td><input class=myinputtextnumber id=mdani style=\"width: 60px;\" onkeypress='return angka_doang(event)' /></td>
                        <td width:100px>Moisture (%)</td><td>:</td><td><input class=myinputtextnumber id=moist style=\"width: 70px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr hidden><td>Dirt (%)</td><td>:</td><td><input class=myinputtextnumber id=dirt style=\"width: 60px;\" onkeypress='return angka_doang(event)' /></td>
                        <td width:100px>Impurities (%)</td><td>:</td><td><input class=myinputtextnumber id=grading style=\"width: 70px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr hidden><td valign=top>".$_SESSION['lang']['toleransi']."</td><td valign=top>:</td><td colspan=5><textarea name=tlransi id=tlransi style=\"width:210px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td></tr>
					</tbody>
				</table>
				
		<td>&nbsp;<td valign=top>	
				<table cellspacing=1 border=0>
					<thead>
                        <tr>
                        <td colspan=3 >".$_SESSION['lang']['syaratPem']."</td>
                        </tr></thead>
					<tbody>
                        <tr>
							<td>".$_SESSION['lang']['payment']."</td>
							<td>:</td>
							<td>
								<select style=\"width: 120px;\" name=syrtByr id=syrtByr >".$optTermin."</select>
							</td>
						</tr>
						<tr hidden>
							<td style='vertical-align:top'>".$_SESSION['lang']['ketbayardep']."</td>
							<td style='vertical-align:top'>:</td>
							<td>
								<textarea name=ketdp id=ketdp style=\"width:100px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea>
							</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>Tata Cara Pembayaran</td>
							<td style='vertical-align:top'>:</td>
							<td>
								<textarea name=ketplns id=ketplns style=\"width:100px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='30'></textarea>
							</td>
						</tr>
                        <tr hidden>
							<td>".$_SESSION['lang']['tanggalbayar']."</td>
							<td>:</td>
							<td>
								<input type=text id=tglByr style=\"width: 116px;\" size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly>
							</td>
                        </tr>
                        <tr>
							<td>Term Bayar</td>
							<td>:</td>
							<td>
								<select style=\"width: 120px;\" name=termbyr id=termbyr >".$opttermbyr."</select>
							</td>
                        </tr>
                        <tr>
							<td>".$_SESSION['lang']['bayarke']."</td>
							<td>:</td>
							<td>
								<select style=\"width: 120px;\" name=byrKe id=byrKe >".$optByrke."</select>
							</td>
						</tr>
                        <tr>
							<td>".$_SESSION['lang']['tndaTangan']."</td>
							<td>:</td>
							<td>
								<select style=\"width: 120px;\" name=tndtng id=tndtng >".$optTtdjual."</select>
							</td>
						</tr>
                        <tr>
							<td>".$_SESSION['lang']['tndaTangan']." 2</td>
							<td>:</td>
							<td>
								<select style=\"width: 120px;\" name=tndtng2 id=tndtng2 >".$optTtdjual."</select>
							</td>
						</tr>
						<tr>
							<td>Tipe Penjualan</td>
							<td>:</td>
							<td>
								<select style=\"width: 120px;\" name=tppenjualan id=tppenjualan >".$opttppembayaran."</select><input type=hidden class=myinputtext id=texttppenjualan name=noext maxlength=30 onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\" />
							</td>
						</tr>
						<tr>
                        <td hidden>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['penjual']."</td><td hidden>:</td><td hidden><input type=text name=tndtngJbtn id=tndtngJbtn class=myinputtext style=\"width: 170px;\" /></td></tr>
						<tr>
                        <td hidden>".$_SESSION['lang']['tandatangan']." ".$_SESSION['lang']['Pembeli']."</td><td hidden>:</td><td hidden><input type=text name=tndtngPembli id=tndtngPembli class=myinputtext style=\"width: 170px;\" /></td></tr>
						<tr>
                        <td hidden>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['Pembeli']."</td><td hidden>:</td><td hidden><input type=text name=jtbnPembli id=jtbnPembli class=myinputtext style=\"width: 170px;\" /></td></tr>

					</tbody>
				</table>
            </table>            
          </fieldset>
          <br />
        <fieldset>
        <legend>".$_SESSION['lang']['lain']."</legend>
     <table>
            <tr hidden> 	 
                 <td valign=top>Force Majuere</td><td valign=top> : </td><td>
				 <textarea onkeypress=\"return tanpa_kutip(event);\" id=forcemajuere style=\"width:830px;height:100px\" rows='5' cols='50' ></textarea></td>
          </tr>
		  <tr hidden> 	 
                 <td valign=top>Perselisihan</td><td valign=top> : </td><td>
				 <textarea onkeypress=\"return tanpa_kutip(event);\" id=perselisihan style=\"width:830px;height:100px\" rows='5' cols='50' ></textarea></td>
          </tr>
         <tr> 	 
                 <td valign=top>".$_SESSION['lang']['lain']."</td><td valign=top> : </td><td>
				 <textarea onkeypress=\"return tanpa_kutip(event);\" id=cttnLain style=\"width:830px;height:100px\" rows='5' cols='50' >".
				 //"Kualitas mutu FFA berasarkan hasil analisa Sucofindo yang sudah ditentukan oleh kedua belah pihak, dimana hasilnya akan dipakai sebagai acuan penetapan mutu barang. Tenggang waktu penyerahan barang maksimal 4 (empat) hari. Penjual dapat melakukan pembatalan penyerahan sepihak bila pembeli tidak melakukan pengangkutan dari tempat yang disepakati dalam batas tenggang waktu. 
				 //Bila kualitas  diluar standar, maka klaim akan ditentukan sbb:
				 //- FFA 5.00%-5.50% harga akan dipotong sebesar Rp 100,-/kg.
				 //- FFA 5.51%-6.00% harga akan dipotong sebesar Rp 200,-/kg.
				 //- FFA 6.01%-6.50% harga akan dipotong sebesar Rp 300,-/kg.
				 //- FFA 6.51%-7.00% harga akan dipotong sebesar Rp 400,-/kg.
				 //- FFA > 7.00% maka pembeli berhak menolak barang.
				 //- Klaim DOBI: (2-DOBI Pemuatan Hasil Analisa Sucofindo)/100 x harga x kuantitas".
				 "</textarea></td>
          </tr>
     
         <tr><td><td><td>
           <button class=mybutton onclick=saveKP()>".$_SESSION['lang']['save']."</button>
           <!--<button class=mybutton onclick=copyFromLast()>".$_SESSION['lang']['copy']."</button>-->
           <button class=mybutton onclick=clearFrom()>".$_SESSION['lang']['new']."</button>

         
     
		</table>
        </fieldset>
		</fieldset>";
echo"<input type=hidden id=method name=method value='insert' /><input type=hidden id=nokontrak_ref name=nokontrak_ref value='' />    ";
/*
<tr>
		<td>".$_SESSION['lang']['ppn']."</td>
		<td>:</td>		
		<td><select id=ppn  style=\"width:150px;\">'".$optppn."'</select></td>
	</tr>	
*/
CLOSE_BOX();
echo"</div>";//<input type=hidden id=method value='insertht'>	


echo "<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span><br>');
$frm[0]='';
$frm[1]='';

$emodul = "PGH";
@$arrmodul = getmodulefil($emodul);
foreach($arrmodul as $key=>$val){
	@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
}


$frm[1].="<fieldset>
		<legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='uploadx' id='uploadx' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='submitfile()'>Submit</button>
					<button class=mybutton onclick='loadfiles()'>Selesai</button>
				</td>
				
			</tr>
		</table></fieldset>";
		
$frm[1].="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center'>".$_SESSION['lang']['nourut']."</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";	


	$frm[0].="<div id='datadetail'></div><div id='listdatadetail'></div>";

$hfrm[0]=strtoupper($_SESSION['lang']['detail']);
$hfrm[1]=strtoupper($_SESSION['lang']['file']);
drawTab('FRM',$hfrm,$frm,100,'auto');   
CLOSE_BOX();
echo"</div>";




echo close_body(); ?>
<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/pmn_kontrakjual.js?v=<?php echo time(); ?>'></script>
<!--deklarasi untuk option-->
<?php
$optCust=$optBrg=$opttipe=$optppn=$optKurs=$optmillcode=$optsatbrg=$optPosisiCtr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt=$optunit=$optNoref=$optTtdjual=$opttppembayaran="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optCustsch=$optPtsch=$optBrgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'"; //echo $sOrg;
//$qOrg=mysql_query($sOrg) or die(mysql_error());
//while($rOrg=mysql_fetch_assoc($qOrg))
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
        $optPt.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
        $optPtsch.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}


$sCust="SELECT a.kodecustomer,a.namacustomer  from ".$dbname.".pmn_4customer a LEFT JOIN ".$dbname.".pmn_4komoditi b ON a.kodecustomer=b.kodecustomer where kodebarang !='40000003' group by kodecustomer order by namacustomer ";
//$qCust=mysql_query($sCust) or die(mysql_error($sCust));
//while($rCust=mysql_fetch_assoc($qCust))
$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
$qCust->setFetchMode(PDO::FETCH_ASSOC);
while($rCust=$qCust->fetch())
{
        $optCust.="<option value=".$rCust['kodecustomer'].">".$rCust['namacustomer']."</option>";
        $optCustsch.="<option value=".$rCust['kodecustomer'].">".$rCust['namacustomer']."</option>";
}

$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." ".$bar['namaorganisasi']."</option>";
}


// $arrKurs=array("IDR","USD");
// foreach($arrKurs as $dt){
	// $optKurs.="<option value=".$dt.">".$dt."</option>";
// }
$str="select * from ".$dbname.".setup_matauang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optKurs.="<option value='".$bar['kode']."'>".$bar['matauang']."</option>";
}


#ambil franco
$optByrke=$optTermin=$optFranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sFranco="select distinct id_franco,franco_name from ".$dbname.".pmn_5franco order by franco_name asc";
//$qFranco=mysql_query($sFranco) or die(mysql_error($conn));
//while($rFranco=mysql_fetch_assoc($qFranco))
$qFranco=$owlPDO->query($sFranco) or die(print " Gagal: ".PDOException::getMessage());
$qFranco->setFetchMode(PDO::FETCH_ASSOC);
while($rFranco=$qFranco->fetch())
                {
	$optFranco.="<option value='".$rFranco['id_franco']."'>".$rFranco['franco_name']."</option>";
}
#termin pembayaran
$sFranco2="select distinct kode from ".$dbname.".pmn_5terminbayar order by kode asc";
//$qFranco2=mysql_query($sFranco2) or die(mysql_error($conn));
//while($rFranco2=mysql_fetch_assoc($qFranco2))
$qFranco2=$owlPDO->query($sFranco2) or die(print " Gagal: ".PDOException::getMessage());
$qFranco2->setFetchMode(PDO::FETCH_ASSOC);
while($rFranco2=$qFranco2->fetch())
                {
	$optTermin.="<option value='".$rFranco2['kode']."'>".$rFranco2['kode']."</option>";
}
$arrStatPPn=array("0"=>"Exclude","1"=>"Include");
$optSat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrStatPPn as $row=>$lstNm){
	$optSat.="<option value='".$row."'>".$lstNm."</option>";
}
$sByr="select * from ".$dbname.".keu_5akunbank order by namabank";
//$qbyr=mysql_query($sByr) or die(mysql_error($conn));
//while($rByr=mysql_fetch_assoc($qbyr))
$qbyr=$owlPDO->query($sByr) or die(print " Gagal: ".PDOException::getMessage());
$qbyr->setFetchMode(PDO::FETCH_ASSOC);
while($rByr=$qbyr->fetch())
{
	$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$rByr['namabank']."'");
	$optByrke.="<option value='".$rByr['noakun']."'>".$rByr['pemilik'].":".$optNamaBank[$rByr['namabank']]." ".$rByr['rekening']."</option>";
}

$optKarid = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

$iTtd="select * from ".$dbname.".pmn_5ttd";
//$nTtd=mysql_query($iTtd) or die(mysql_error($conn));
//while($dTtd=mysql_fetch_assoc($nTtd))
$nTtd=$owlPDO->query($iTtd) or die(print " Gagal: ".PDOException::getMessage());
$nTtd->setFetchMode(PDO::FETCH_ASSOC);
while($dTtd=$nTtd->fetch())
                {
	$optTtdjual.="<option value='".$dTtd['nama']."'>".$optKarid[$dTtd['nama']]."</option>";
}

## LIST POSISI Kontrak
$str = "select * from ".$dbname.".pmn_5lokasikontrak where status='1'";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optPosisiCtr.="<option value='".$val['id']."'>".$val['inisial']."-".$val['lokasi']."</option>";
}

## LIST DAERAH Kontrak
$str = "select * from ".$dbname.".pmn_5daerahkontrak where status='1'";
$opttermbyr=$optDaerahCtr = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optDaerahCtr.="<option value='".$val['id']."'>".$val['lokasi']."</option>";
}
// get enum pmn_kontrakjual
// $optjns=array("PM"=>"Pengiriman","PK"=>"Pemenuhan Kontrak","UM"=>"Uang Muka","BA"=>"Berita Acara Serah Terima"); 
// $arrtermbyr=getEnum($dbname,'pmn_kontrakjual','termbayar');
// foreach($arrtermbyr as $kei=>$fal)
// {
    // $opttermbyr.="<option value='".$kei."'>".$optjns[$kei]."</option>";
// } 

$arrtermbyr=array("BA"=>"Berita Acara Serah Terima"); 
// $arrtermbyr=array("PM"=>"Pengiriman","BA"=>"Berita Acara Serah Terima"); 
foreach($arrtermbyr as $kei=>$fal){
    $opttermbyr.="<option value='".$kei."'>".$fal."</option>";
} 


$tppembayaran=array("FOB"=>"FOB","CIF"=>"CIF","CNF"=>"COST AND FREIGHT","FRANCO"=>"FRANCO","LOCO"=>"LOCO"); 
foreach($tppembayaran as $val)
{
    $opttppembayaran.="<option value='".$val."'>".$val."</option>";
}   

$tppembayaran=array("JUAL"=>"JUAL"); 
foreach($tppembayaran as $val)
{
    $opttipejualbeli.="<option selected value='".$val."'>".$val."</option>";
}   

$str="select * from ".$dbname.".organisasi where tipe='PABRIK'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optmillcode.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'",'2');
foreach ($nmkomoditi as $key => $value) {
	$optBrgsch.="<option value='".$key."'>".explode('-',$value)[1]."</option>";
}
?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pmn_kontrakjual').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	echo"<table>";
	echo"
	
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>		
				<td><select class='select2'  name=kodeptsch id=kodeptsch style='width:154px'>".$optPtsch."</select></td>
				
				<td>".$_SESSION['lang']['nmcust']."</td>
				<td> : </td>
				<td><select class='select2' id=kodecustomersch name=kodecustomersch style=\"width:155px;\">".$optCustsch."</select></td>
             
				<td>".$_SESSION['lang']['produk']."</td>
				<td> : </td>
				<td><select class='select2' id=produksch name=produksch style=\"width:155px;\">".$optBrgsch."</select></td>
             
			</tr>
			<tr>
				<td>".$_SESSION['lang']['NoKontrak']."</td>
				<td>:</td>		
				<td>
					<input type=text id=nokontraksch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>			
				</td>
			</tr>			
			
			<tr>
            <td colspan=3><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
        </tr>
	";
        echo "</table>";
echo"</fieldset></td>";
echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
// echo"<div id=listdata style=display:none>";//buka list data
echo"<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    
            <table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					
          <th align=center>".$_SESSION['lang']['NoKontrak']."</th>
          <th align=center>".$_SESSION['lang']['nm_perusahaan']."</th>
          <th align=center>".$_SESSION['lang']['nmcust']."</th>
          <th align=center>".$_SESSION['lang']['tglKontrak']."</th>
          <th align=center>".$_SESSION['lang']['produk']."</th>
          <th align=center>Tanggal Kirim</th>
          <th align=center>Term Bayar</th>
          <th align=center>".$_SESSION['lang']['tipetransaksi']."</th>
          <th align=center>".$_SESSION['lang']['updateby']."</th>
					<th align=center colspan=6>".$_SESSION['lang']['action']."</th>
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
	";
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span>');
// echo "<fieldset style=float:left>

/*
	<td>".$_SESSION['lang']['kodeunit']."</td>
				<td> : </td>
				<td><select class='select2'  name=millcode id=millcode style='width:204px'>".$optmillcode."</select></td>
*/
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
				
				<td>".$_SESSION['lang']['pabrik']."</td>
				<td> : </td>
				<td>
					<select class='select2' style=\"width: 200px;\" name=millcode id=millcode >".$optmillcode."</select>
				</td>
				
				<td>".$_SESSION['lang']['tglKontrak']."</td>
				<td> : </td>
				<td><input type=text style=\"width: 200px;\" id=tlgKntrk size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)  readonly/></td>
            </tr>
			
            <tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td> : </td>
				<td><select class='select2' style=\"width:200px;\" id=kdPt name=kdPt onchange='getRek()'>".$optPt."</select></td>
				
				<td>".$_SESSION['lang']['nmcust']."</td>
				<td> : </td>
				<td><select class='select2' id=custId name=custId style=\"width:200px;\" onchange=\"getDataCust(0)\">".$optCust."</select></td>
             
				<td>Asal Komoditi Kontrak</td><td> : </td>
				<td><select class='select2' style=\"width:200px;\" id=daerahctr name=daerahctr >".$optDaerahCtr."</select></td>
            </tr>
           
			<tr> 	 
				<td hidden>".$_SESSION['lang']['NoKontrak']." SPJB</td>
				<td hidden>:</td>
				<td hidden><input type=text class=myinputtext id=noext name=noext maxlength=30 onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\" /></td>
				
				<td>Berikat</td>
				<td> : </td>
				<td><input type=checkbox id=berikat >".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."</td>
				
				<td>% PPN</td>
				<td> : </td>
				<td><input type=text class=myinputtextnumber  name=persenppn id=persenppn onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('persenppn',2);\"   style=\"width:200px;\" />
				</td>
				
			</tr>
			
			
			<tr hidden>
				<td>".$_SESSION['lang']['noreferensi']."</td>
				<td> : </td>
				<td style='padding-right:10px'>
					<input type=text class=myinputtext id=noreferensi name=noreferensi maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\" disabled />
					<img src=images/zoom.png title='".$_SESSION['lang']['find']."' class=resicon onclick=carinorefrensi('".$_SESSION['lang']['find']."',event)>
				</td>
				
				<td>Contact Person :</td>
				<td><select class='select2'  id=nmPerson style=\"width:150px;\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
				<td>".$_SESSION['lang']['nokontrakinduk']."</td><td style='display:none'><select class='select2' style=\"width:150px;\" id='kntrkRef'>".$optNoref."</select></td>
				
			
				 <td>Lokasi (No. Kontrak)</td>
				 <td> : </td>
				 <td><select class='select2' style=\"width:100px;\" id=posisictr name=posisictr >".$optPosisiCtr."</select></td>
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
					<td align=center>Harga Spot </br> (Diinput jika ada)</td>
					<td align=center>".$_SESSION['lang']['matauang']."</td>
					<td align=center>".$_SESSION['lang']['jmlhBrg']."</td>
					<td align=center>PPN</td>
					<td style=\"width:350px;\">".$_SESSION['lang']['terbilang']."</td>
				</tr>
				</thead>
				<tbody>
					<td><select class='select2' id=kdBrg name=kdBrg onchange=\"getSatuan(0,0,0)\" style=\"width:150px;\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
					<td><select class='select2' id=stn name=stn style=\"width:50px;\"><option value=''></option></select></td>
					<td><input type=text class=myinputtextnumber  name=HrgStn id=HrgStn onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('HrgStn',2);\"  onblur=\"hitungHarga()\" style=\"width:70px;\" /></td>
					<td><input type=text class=myinputtextnumber  name=hrgspot id=hrgspot onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('hrgspot',2);\" style=\"width:70px;\" /></td>
					<td><select class='select2' id=kurs name=kurs style=\"width:60px;\">".$optKurs."</select></td>
					<td><input type=text class=myinputtextnumber name=jmlh id=jmlh onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" onkeyup=\"z.numberFormat('jmlh',2);getBerat();\"  onblur=\"hitungHarga()\" />
					<input id=tmpHarga type=hidden value=0>
					</td>
					<td><select class='select2' id=ppnId name=ppnId style=\"width:80px;\">".$optSat."</select></td>
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
                        <td>".$_SESSION['lang']['tempatpenyerahan']."</td><td>:</td><td colspan=5><select class='select2' name=tmbngn id=tmbngn style=\"width: 230px;\" >".$optFranco."</select></td></tr>
                        <tr><td>FFA (%)</td><td>:</td><td><input class=myinputtextnumber id=ffa style=\"width: 60px;\" onkeypress='return angka_doang(event)' /></td>
                        <td width:100px>DOBI</td><td>:</td><td><input class=myinputtextnumber id=dobi style=\"width: 70px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr><td>M & I (%)</td><td>:</td><td><input class=myinputtextnumber id=mdani style=\"width: 60px;\" onkeypress='return angka_doang(event)' /></td>
                        <td width:100px>Moisture (%)</td><td>:</td><td><input class=myinputtextnumber id=moist style=\"width: 70px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr><td>Dirt (%)</td><td>:</td><td><input class=myinputtextnumber id=dirt style=\"width: 60px;\" onkeypress='return angka_doang(event)' /></td>
                        <td width:100px>Impurities (%)</td><td>:</td><td><input class=myinputtextnumber id=grading style=\"width: 70px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr><td hidden valign=top>".$_SESSION['lang']['toleransi']."</td><td hidden valign=top>:</td>
						<td hidden colspan=5><textarea name=tlransi id=tlransi style=\"width:210px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td></tr>
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
								<select class='select2' style=\"width: 120px;\" name=syrtByr id=syrtByr >".$optTermin."</select>
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
								<textarea name=ketplns id=ketplns style=\"width:100px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea>
							</td>
						</tr>
                        <tr>
							<td>".$_SESSION['lang']['tanggalbayar']."</td>
							<td>:</td>
							<td>
								<input type=text id=tglByr style=\"width: 116px;\" size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this) readonly>
							</td>
							<td><input type=checkbox id=ketbyr> <span style='color:blue;font-size:10px;'><i>Jika dicentang, tanggal bayar di PDF akan ditampilkan</i></span></td>
                        </tr>
                        <tr>
							<td>Term Bayar</td>
							<td>:</td>
							<td>
								<select class='select2' style=\"width: 120px;\" name=termbyr id=termbyr >".$opttermbyr."</select>
							</td>
                        </tr>
                        <tr>
							<td>".$_SESSION['lang']['bayarke']."</td>
							<td>:</td>
							<td>
								<select class='select2' style=\"width: 120px;\" name=byrKe id=byrKe >".$optByrke."</select>
							</td>
						</tr>
                        <tr>
							<td>".$_SESSION['lang']['tndaTangan']."</td>
							<td>:</td>
							<td>
								<select class='select2' style=\"width: 120px;\" name=tndtng id=tndtng >".$optTtdjual."</select>
							</td>
						</tr>
						<tr>
							<td>Tipe Penjualan</td>
							<td>:</td>
							<td>
								<select class='select2' style=\"width: 120px;\" name=tppenjualan id=tppenjualan >".$opttppembayaran."</select>
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
			<legend>Catatan Mutu :</legend>
				<table>
 
					<tr> 	 
							<td valign=top>M&I 1</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=md_1 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>FFA 1</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=ffa_1 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>DOBI 1</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=dobi_1 style=\"width:200px\" rows='5' cols='50' ></input></td>
					</tr>		
					<tr> 	 
							<td valign=top>M&I 2</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=md_2 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>FFA 2</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=ffa_2 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>DOBI 2</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=dobi_2 style=\"width:200px\" rows='5' cols='50' ></input></td>
					</tr>		
					<tr> 	 
							<td valign=top>M&I 3</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=md_3 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>FFA 3</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=ffa_3 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>DOBI 3</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=dobi_3 style=\"width:200px\" rows='5' cols='50' ></input></td>
					</tr>		
					<tr> 	 
							<td valign=top>M&I 4</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=md_4 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>FFA 4</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=ffa_4 style=\"width:200px\" rows='5' cols='50' ></input></td>
							<td valign=top>DOBI 4</td><td valign=top> : </td>
							<td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" id=dobi_4 style=\"width:200px\" rows='5' cols='50' ></input></td>
					</tr>		
				</table>
			</fieldset>
			<fieldset>
			<legend>Lainnnya</legend>
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
							"</textarea></td>
					</tr>
				
					<tr><td><td><td>
					<button class=mybutton onclick=saveKP()>".$_SESSION['lang']['save']."</button>
					<!--<button class=mybutton onclick=copyFromLast()>".$_SESSION['lang']['copy']."</button>-->
					<button class=mybutton onclick=clearFrom()>".$_SESSION['lang']['new']."</button>
		
				</table>
			</fieldset>
		</fieldset>";
echo"<input type=hidden id=method name=method value='insert' />    ";
/*
<tr>
		<td>".$_SESSION['lang']['ppn']."</td>
		<td>:</td>		
		<td><select class='select2' id=ppn  style=\"width:150px;\">'".$optppn."'</select></td>
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
					<select class='select2' id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
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
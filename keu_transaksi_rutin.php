<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_transaksi_rutin')."</span>"); 
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/keu_transaksi_rutin.js?v=<?php echo time(); ?>'></script>

<?php

#Tipe Transaksi
$optunit=$optnoakun=$optak=$optnoso=$optvhc=$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optjenistransaksi=$opttipe='';
$arrtipe=getEnum($dbname,'keu_transaksi_rutin','tipe_transaksi');
foreach($arrtipe as $kei=>$fal)
{
    $opttipe.="<option value='".$kei."'>".$fal."</option>";
}

// $arrtipe=getOrgDetail(1);
// foreach($arrtipe as $kei=>$fal){
    // $sBank="select * from ".$dbname.".keu_5akunbank where pemilik='".$kei."'";
    // $rBank=fetchData($sBank);
    // if(count($rBank)!=0){
        // $optunit.="<option value='".$kei."'>".$kei." - ".$fal."</option>";
    // }
// }


#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
	if($val==$_SESSION['empl']['lokasitugas']){
		$optunit.="<option value='".$val."' selected>".$val." - ".$nama."</option>";
	}else{
		$optunit.="<option value='".$val."' >".$val." - ".$nama."</option>";
	}
} 



// $optjenistransaksi.="<option value='tagihan'>Tagihan</option>";
$optjenistransaksi.="<option value='prepaid'>Prepaid</option>";

#akuun debet
$sakundbt=$owlPDO->query("select distinct noakun,namaakun from ".$dbname.".keu_5akun where (noakun like '21401%'  or  noakun like '%11401%' or noakun like '%12101%') and detail=1
        order by namaakun asc");
$sakundbt->setFetchMode(PDO::FETCH_ASSOC);
$optDebet='';
while($rakun=  $sakundbt->fetch()){
    $optDebet.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
}

$sakundbt=$owlPDO->query("select distinct noakun,namaakun from ".$dbname.".keu_5akun where (noakun like '1130100%' or  noakun like '%11401%' or noakun like '%12101%') and char_length(noakun)=7
        order by namaakun asc");
$sakundbt->setFetchMode(PDO::FETCH_ASSOC);
$optKredit='';
while($rakun= $sakundbt->fetch()){
    $optKredit.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
}

echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>";
echo"<table>
<tr>
	<td>".$_SESSION['lang']['notransaksi']."</td>
	<td>:</td>
	<td><input type=text id=notranscr size=18 maxlength=30 class=myinputtext></td>
	
		<td>".$_SESSION['lang']['keterangan']."</td>
	<td>:</td>
	<td><input type=text id=keterangancr size=18  class=myinputtext></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['nodok']."</td>
	<td>:</td>
	<td><input type=text id=nodokumencr size=18 class=myinputtext></td>
</tr>
<tr>
	<td></td>
	<td></td>
	<td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
</tr>
</table>";

		 

echo"";
echo"</fieldset></td>
     </tr>
         </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
 echo"<div class=table-scroll style='height:350px'>";
// echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr align=center><th>".$_SESSION['lang']['nourut']."</th>";
echo"<th>".$_SESSION['lang']['notransaksi']."</th>";
echo"<th>".$_SESSION['lang']['kodeorg']."</th>";
// echo"<th>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['transaksi']."</th>";
// echo"<th>".$_SESSION['lang']['tipetransaksi']."</th>";
// echo"<th>".$_SESSION['lang']['tipewaktu']."</th>";
echo"<th>".$_SESSION['lang']['supplier']."</th>";
// echo"<th>".$_SESSION['lang']['noakun']." Bank</th>";
echo"<th>".$_SESSION['lang']['noakun']." Debet</th>";
echo"<th>".$_SESSION['lang']['noakun']." Kredit</th>";
echo"<th>".$_SESSION['lang']['tanggalmulai']."</th>";
echo"<th>".$_SESSION['lang']['tanggalselesai']."</th>";
echo"<th>".$_SESSION['lang']['jumlah']."</th>";
echo"<th>".$_SESSION['lang']['tenor']."</th>";
echo"<th>".$_SESSION['lang']['bulanan']."</th>";
echo"<th>".$_SESSION['lang']['keterangan']."</th>";
echo"<th colspan=4>".$_SESSION['lang']['action']."</th>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table>";
echo"</div></div><input type=hidden id=proses value=insert />";


$optjnstipe=$opttipewaktu='';
$str="select kode,namajenis from ".$dbname.".keu_5jenistagihan where transaksirutin=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optjnstipe.="<option value='".$bar['kode']."'>".$bar['kode']." - ".$bar['namajenis']."</option>";
}

$arrtipe=getEnum($dbname,'keu_transaksi_rutin','tipewaktu');
foreach($arrtipe as $kei=>$fal){
	$opttipewaktu.="<option value='".$kei."'>".$fal."</option>";
   
}

// $res=$owlPDO->query("select b.supplierid, b.namasupplier from ".$dbname.".log_5supkelompok a 
// left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where b.status=1");
$str="select * from ".$dbname.".log_5supplier where status=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optsup.="<option value='".$bar['supplierid']."'>".$bar['supplierid']." - ".$bar['namasupplier']."</option>";
}
	

$str="select * from ".$dbname.".keu_5akun where detail=1 and kasbank=0";
$res=fetchdata($str);
foreach($res as $bar){
	$optak.="<option value='".$bar['noakun']."'>[".$bar['noakun']."] ".$bar['namaakun']."</option>";
}



echo"<div id=formInput style=display:none;>
    <input type=hidden id=method value='insert'/>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend><table border=0 >";
echo"<tr>
		<td>".$_SESSION['lang']['notransaksi']." &nbsp;&nbsp;&nbsp;</td>
		<td>:</td>
		<td><input type=text id=notransaksi class=myinputtext style=width:153px; disabled></td>
		<td>Pihak Ketiga</td><td>:</td><td><select id=pihakketiga style=width:153px;>".$optsup."</select>
		<img id=pihakketiga onclick=z.elSearch('pihakketiga',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
		<img src='images/obl.png' title='Obligatory'></td>
	
	 </tr>";
echo"</tr>	
		<td>".$_SESSION['lang']['unit']." &nbsp;&nbsp;&nbsp;</td>
		<td>:</td>
		<td><select id=unit style=width:157px; onchange=getoption();>".$optunit."</select><img src='images/obl.png' title='Obligatory'></td>
		<td>".$_SESSION['lang']['kodevhc']."</td><td>:</td><td><select id=kodevhc style=width:153px;>".$optvhc."</select></td>
		 </tr>"; 
	 //onchange='getform()'
echo"<tr hidden>
		<td>".$_SESSION['lang']['tipetransaksi']." &nbsp;&nbsp;&nbsp;</td><td>:</td>
		<td><select id=tipetransaksi style=width:157px; >".$opttipe."</select><img src='images/obl.png' title='Obligatory'></td>
	 </tr>";
	 echo"<tr hidden>
		<td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['transaksi']." &nbsp;&nbsp;&nbsp;</td><td>:</td>
		<td><select id=jenistransaksi style=width:157px;>".$optjenistransaksi."</select><img src='images/obl.png' title='Obligatory'></td>
	 </tr>";
	 
	 
	 
	 
	 
	  echo"<tr hidden><td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['tipe']."</td><td>:</td><td><select id=jenistipe style=width:153px;>".$optjnstipe."</select><img src='images/obl.png' title='Obligatory'></td></tr>";

        echo"<tr><td>".$_SESSION['lang']['nodok']."</td><td>:</td><td><input type=text id=nodokumen value='".$barht['nodokumen']."' class=myinputtext style=width:150px; placeholder='No.Polis/No.Kontrak' ></td>
                 <td>".$_SESSION['lang']['noso']."</td><td>:</td><td><select id=noso style=width:153px;>".$optnoso."</select></td></tr>";
       
       
		echo"<tr>
				<td>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tglmulai onmousemove=setCalendar(this.id) onkeypress=return false; onchange=gettotbulan(); style=width:150px; maxlength=10 /><img src='images/obl.png' title='Obligatory'></td>
                
				<td>".$_SESSION['lang']['tanggalselesai']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tglselesai onmousemove=setCalendar(this.id) onkeypress=return false; onchange=gettotbulan(); style=width:150px; maxlength=10 /><img src='images/obl.png' title='Obligatory'></td></tr>";
		
		echo"<tr><td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['rupiah']."</td><td>:</td><td><input type=text id=totrup class=myinputtextnumber onkeyup=\"z.numberFormat('totrup',2); return getrpperbulan()\" style=width:150px; onkeypress=\"return angka_doang(event);\" ><img src='images/obl.png' title='Obligatory'></td>
				 <td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['bulan']."</td><td>:</td><td><input type=text id=totbln class=myinputtextnumber  style=width:150px; onkeypress=\"return angka_doang(event);\" onkeyup='getrpperbulan()' disabled><img src='images/obl.png' title='Obligatory'></td></tr>";
		echo"<tr><td>Rp/".$_SESSION['lang']['bulan']."</td><td>:</td><td><input type=text id=rpperbulan class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event);\" disabled><img src='images/obl.png' title='Obligatory'></td>
				 <td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><input type=text id=keterangan class=myinputtext style=width:150px; ><img src='images/obl.png' title='Obligatory'></td></tr>";
		echo"<tr>
					<td>".$_SESSION['lang']['akun']." ".$_SESSION['lang']['debet']."</td>
					<td>:</td>
					<td nowrap><select id=debit style=width:153px;>".$optak."</select>
						<img id='debit' onclick=z.elSearch('debit',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'><img src='images/obl.png' title='Obligatory' style='position:relative;top:3px;left:1px;'>
						</td>
					
					<td>".$_SESSION['lang']['akun']." ".$_SESSION['lang']['kredit']."</td>
					<td>:</td>
					<td nowrap><!--<input type=text id=kredit class=myinputtext style=width:150px; disabled>--><select id=kredit style=width:153px;>".$optak."</select>
					<img id='kredit' onclick=z.elSearch('kredit',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'><img src='images/obl.png' title='Obligatory'></td>
				 </tr>";
		echo"<tr><td><td><td><button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
		         <button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button></td></tr>";
	 
	 	   echo"<tr hidden>
		   <td>".$_SESSION['lang']['tipewaktu']."</td><td>:</td><td><select id=tipewaktu style=width:153px;>".$opttipewaktu."</select><img src='images/obl.png' title='Obligatory'></td>
		   <td>No. Akun Bank</td><td>:</td><td><select id=noakun style=width:153px;>".$optnoakun."</select><img src='images/obl.png' title='Obligatory'></td></tr>";
	 
	 echo"</table>";
echo"<div id=formdt style=display:none;>";
echo"</div>";
echo"</fieldset>"; 
// if ($_SESSION['language'] == 'ID') {
// echo"<fieldset style='text-align:left;height:80px;width:225px'>
    // <legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
    // Pastikan Unit Sudah Terdaftar pada menu <b>Keuangan - Setup - Daftar Rek Bank Perusahaan</b>.
    // </fieldset>";
// }else{
    // echo"<fieldset style='text-align:left;height:80px;width:225px'>
    // <legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
    // Please register unit at <b>Finance - Setup - Daftar Rek Bank Perusahaan</b>.
    // </fieldset>";
// }
echo"</div>";
CLOSE_BOX();
echo close_body(); ?>

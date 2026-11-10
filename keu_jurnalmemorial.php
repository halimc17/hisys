<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src='js/zSelect2.js?ver=1'></script>
<style>
/* dropdown select2 di atas dialog */
body > .select2-container{z-index:2100}
</style>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/keu_jurnalmemorial.js?v=<?php echo time(); ?>'></script>
<!--deklarasi untuk option-->

<?php
$optunit=$opttipetransaksi=$optalokasi=$optcustomer=$optsupplier=$optakundt=$optkegiatan=$optnik=$optadk=$optvhc ="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optstatsch=$optunitsch=$opttipetransaksisch=$optrevisisch="<option value=''>".$_SESSION['lang']['all']."</option>";
// =$optmatauang
#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
    $optunitsch.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

#= mata uang
$str="select * from ".$dbname.".setup_matauang where kode='IDR'";
$res=fetchdata($str);
foreach($res as $bar){
	$optmatauang="<option value='".$bar['kode']."' selected>".$bar['matauang']."</option>";
	$defaultkurs='1';
}

$emodul = "JM";
@$arrmodul = getmodulefil($emodul);
foreach($arrmodul as $key=>$val){
	@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
}

$optflaghutangunit='';
$arrtransaksi=array("0"=>"Tidak","1"=>"Ya"); 
foreach($arrtransaksi as $val=>$nama){
    $optflaghutangunit.="<option value='".$val."'>".$nama."</option>";
}  

$str="select * from ".$dbname.".setup_kegiatan where status=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optkegiatan.="<option value='".$bar['kodekegiatan']."'>".$bar['kelompok']." - ".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
}

$str="select * from ".$dbname.".pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$optcustomer.="<option value='".$bar['kodecustomer']."'>".$bar['kodecustomer']." - ".$bar['namacustomer']."</option>";
}

$str="select * from ".$dbname.".log_5supplier where status=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optsupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
}
$arrstat=array("0"=>$_SESSION['lang']['belumposting'],"1"=>$_SESSION['lang']['posting']); 
foreach($arrstat as $val=>$nama){
    @$optstatsch.="<option value='".$val."'>".$nama."</option>";
}  

$arrstat=array("JM"=>$_SESSION['lang']['jurnalmemo'],"JA"=>$_SESSION['lang']['jurnaladjustment']); 
foreach($arrstat as $val=>$nama){
    @$opttipetransaksisch.="<option value='".$val."'>".$nama."</option>";
    @$opttipetransaksi.="<option value='".$val."'>".$nama."</option>";
}  

for($i=0;$i<=5;$i++){
	@$optrevisi.="<option value='".$i."'>".$i."</option>";
    @$optrevisisch.="<option value='".$i."'>".$i."</option>";
}


#<!--HEADER UNTUK BUAT BARU SAMA LIST-->

// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('keu_jurnalmemorial').'</span>');
echo"<table border=0>
     <tr >
	 <td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	<td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
		<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	<td>
	<td align=center style='width:70px;height:10px!important;cursor:pointer;' onclick=showformupload()>
		<img class=delliconBig src=images/edit.png title='Upload Excel'><br>Upload Excel</td>
	<td>
	 <fieldset><legend>".$_SESSION['lang']['find']."</legend>";
	echo"<table cellspacing=2 cellpadding=1>
		<tr>
			<td>".$_SESSION['lang']['nojurnal']."</td>
			<td>:</td>
			<td><input type=text id=nojurnalsch class=myinputtext style=\"width:190px;\"></td>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select class=select2 id=kodeorgsch style=\"width:200px;\">".$optunitsch."</select></td>
			<td>".$_SESSION['lang']['tipetransaksi']."</td>
			<td>:</td>
			<td><select class=select2 id=tipetransaksisch style=\"width:200px;\">".$opttipetransaksisch."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=\"width:84px;\"/>
				s/d
				<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=\"width:84px;\"/>
			</td>
			<td>".$_SESSION['lang']['noreferensi']."</td>
			<td>:</td>
			<td><input type=text id=noreferensisch class=myinputtext style=\"width:190px;\"></td>
			<td>".$_SESSION['lang']['revisi']."</td>
			<td>:</td>
			<td><select class=select2 id=revisisch style=\"width:200px;\">".$optrevisisch."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['status']."</td>
			<td>:</td>
			<td><select class=select2 id=statsch style=\"width:190px;\">".$optstatsch."</select></td>
			<td colspan=6 style='padding-top:4px'>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				<button class=mybutton onclick=tarikJM('excel')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=tarikJM('pdf')>PDF</button>
			</td>
		</tr>
	</table>";
echo"</fieldset></td>";

echo"<td valign=top><fieldset><legend>".$_SESSION['lang']['info']."</legend>";
echo"<button class=mybutton onclick=showhideinfo()>".$_SESSION['lang']['lihat']."</button>";
echo"<div id=forminfo style=display:none;overflow:auto;max-width:740px;max-height:60px;>";
echo"<fieldset>
<legend>Info</legend>
<ol>
	<li>Untuk Akun yang terdaftar dijurnal memorial dapat di setting dimenu : keuangan->setup->daftar perkiraan</li>
	<li>Untuk posisi kredit saat input angka, ditambahkan tanda - (minus)</li>
	<li>Approval dapat disetting dimenu setup->approval dengan tipe Jurnal Memorial</li>
</ol>
</fieldset>";
echo"</div>";
echo"</fieldset></td>";
echo" </tr>";
echo"
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
// echo"<div id=listdata style=display:none>";//buka list data
echo"<div id=listdata style=display:block>";//buka list data
// OPEN_BOX();
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br>');
    echo "<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
					<td  align=center>".$_SESSION['lang']['nourut']."</td>
					<td  align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td  align=center>".$_SESSION['lang']['tipetransaksi']."</td>
					<td  align=center>".$_SESSION['lang']['revisi']."</td>
                    <td  align=center>".$_SESSION['lang']['tanggal']."</td>
                    <td  align=center>".$_SESSION['lang']['unit']."</td>
                    <td  align=center>".$_SESSION['lang']['debet']."</td> 
                    <td  align=center>".$_SESSION['lang']['kredit']."</td> 
                    <td  align=center>Balance</td> 
                    <td  align=center>".$_SESSION['lang']['noreferensi']."</td> 
					<td  align=center>".$_SESSION['lang']['dibuatoleh']."</td> 
                    <td  align=center>".$_SESSION['lang']['updatedby']." </td>
                    <td  align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['persetujuan']."</td> 
                    <td  align=center colspan=5>".$_SESSION['lang']['action']." </td> 
                   
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
// echo "<div id=header style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');

//jumlah dan kurs no 1 dan 2, agar mudah remove comma di js
$arrht="###nojurnal###matauang###kurs###kodeorg###noreferensi###tanggal###tipetransaksi###revisi";

$style=" style=\"width:150px;\" ";

echo "<fieldset>";
// echo "<fieldset style=float:left>";
echo "<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>


	<tr>
		<td ".$style.">".$_SESSION['lang']['nojurnal']."</td>
		<td>:</td>		
		<td><input type=text id=nojurnal size=20 disabled class=myinputtext style=\"width:150px;\"></td>
		
		<td  ".$style.">".$_SESSION['lang']['matauang']." & ".$_SESSION['lang']['kurs']."</td>
		<td>:</td>		
		<td>
			<select class=select2 id=matauang  style=\"width:95px;\" onchange=getkurs()>".$optmatauang."</select>
			<input class=myinputtextnumber id=kurs disabled style=\"width:50px;\" onkeypress='return angka_doang(event)' />
		</td>
		<td>".$_SESSION['lang']['tipetransaksi']."</td>
		<td>:</td>		
		<td>
			<select class=select2 id=tipetransaksi onchange=getrevisi() style=\"width:154px;\">".$opttipetransaksi."</select>
		</td>
	</tr>


	
	<tr>
		<td ".$style.">".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select class=select2 id=kodeorg  style=\"width:154px;\">".$optunit."</select>
		</td>
		<td ".$style.">".$_SESSION['lang']['noreferensi']."</td>
		<td>:</td>		
		<td><input type=text id=noreferensi size=20  class=myinputtext style=\"width:150px;\"></td>
		<td>".$_SESSION['lang']['revisi']."</td>
		<td>:</td>		
		<td>
			<select class=select2 id=revisi  style=\"width:154px;\">".$optrevisi."</select>
		</td>
	</tr>
	
	<tr>
		<td ".$style.">".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal name=tanggal  style=\"width:150px;\" readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	
		</td>
	</tr>
	<tr>
		<td align=center colspan=2></td>
		<td>
			<button class=mybutton onclick=saveht('".$arrht."')>".$_SESSION['lang']['save']."</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo"</div>";//<input type=hidden id=method value='insertht'>	


#= Form Upload Excel =#
echo"<div id=inputdata style=display:none>";
OPEN_BOX();
echo"
	<fieldset style='float:left'><legend>" . $_SESSION['lang']['form'] . " Upload Excel</legend>
		<table border=0 cellspacing=2>
			<tr>
				<td>" . $_SESSION['lang']['file'] . "</td>
				<td>:</td>
				<td><input type='file' name='upload' id='upload' accept='.xls,.xlsx'></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=fileSelected('') style='color:blue;'>Preview</button>
					<a href='keu_jurnalmemorial_slave.php?method=downloadtemplate' class=mybutton id=formuploaddt style='text-decoration:none;display:inline-block;'>Download Template</a>
				</td>
			</tr>
		</table>
	</fieldset>
	";
CLOSE_BOX();
echo "</div>";


OPEN_BOX();
$bulan=range(1,12);

#untuk inputan baru
echo"<div id=contdetail style=display:none; class='table-scroll'>";
echo"</div>";

#list data
echo"<div id=listData style=display:block>";
echo"<div id=containupload>";
echo "</div>";
echo "</div>";

CLOSE_BOX();
#= End =#


#- <!--UNTUK BUAT FORM INPUT HEADER-->
$arrdt="###jumlah###noakun###nodok###keterangan###ketjumlah";
$arrdt.="###kodekegiatan###kodeasset###nik###kodecustomer###kodesupplier###kodevhc###kodeblok";
$arrdt.="###methoddt###nourut";
$arrdt.="###nojurnal###matauang###kurs###kodeorg###noreferensi###tanggal";
$border='0';
echo "<div id=detail style=display:none>";
// echo "<div id=detail style=display:block>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span><br>');
$frm[0]='';
$frm[1]='';


/*
$frm[0].="<fieldset>
<legend><b>Info Tools</b></legend>
<ol>
	<li>Tombol tools berguna untuk memudahkan dalam penginputan jika sumber data didapat dari transaksi AP / AR</li>
	<li>Jika tipe transaksi <b>keluar</b>, maka tombol AR tidak dapat digunakan, sebaliknya jika tipe transaksi <b>masuk</b>, maka tombol AP tidak dapat digunakan</li>
	<li>Tombol AP, bersumber dari transaksi tagihan AP / invoice AP dimenu : Keuangan->Transaksi->Tagihan (AP)</li>
	<li>Tombol AR, bersumber dari transaksi penagihan AR / invoice AR dimenu : Keuangan->Transaksi->Penagihan (AR)</li>
</ol>
</fieldset>";
*/

// echo "<fieldset>";
$frm[0].="<fieldset>";
$frm[0].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";

	$frm[0].="<table cellspacing=1 border=".$border.">
			<tr>
				<td ".$style.">".$_SESSION['lang']['nodok']."</td>
				<td> : </td>
				<td><input type=text id=nodok class=myinputtext style=\"width:150px;\"></td>
				
			
				<td  ".$style.">".$_SESSION['lang']['namakegiatan']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=kodekegiatan  style=\"width:154px;\">".$optkegiatan."</select>
				</td>
			</tr>
			
			<tr>
			<td ".$style.">".$_SESSION['lang']['akun']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=noakun  style=\"width:154px;\" onchange=getkodekegiatanalokasi()>".$optakundt."</select>
				</td>
				
				<td>".$_SESSION['lang']['aktivadalam']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=kodeasset  style=\"width:154px;\">".$optadk."</select>
				</td>
			</tr>
			
			<tr>
			
				<td ".$style.">".$_SESSION['lang']['status']."</td>
				<td>:</td>
				<td>
					<select class=select2 id=ketjumlah style=\"width:154px;\">
						<option value=debet>Debet</option>
						<option value=kredit>Kredit</option>
					</select>
				</td>
				
				<td ".$style.">".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=nik  style=\"width:154px;\">".$optnik."</select>
				</td>
			</tr>
			
			<tr>
				<td ".$style.">".$_SESSION['lang']['jumlah']."</td>
				<td>:</td>
				<td>
					<input class=myinputtextnumber id=jumlah style=\"width:150px;\" onkeyup=z.numberFormat('jumlah',2); onkeypress='return_tanpa_kutip_dan_sepasi(event)' />
				</td>

				<td ".$style.">".$_SESSION['lang']['customer']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=kodecustomer  style=\"width:154px;\">".$optcustomer."</select>
				</td>	
			</tr>
			
			<tr>			
				<td valign=top rowspan=3 ".$style.">".$_SESSION['lang']['keterangan']."</td>	
				<td valign=top rowspan=3>:</td>
				<td rowspan=3  valign=top><textarea rows='3' id=keterangan placeholder='keterangan' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:300px;\"></textarea></td>
		
			
				<td ".$style.">".$_SESSION['lang']['namasupplier']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=kodesupplier  style=\"width:154px;\">".$optsupplier."</select>
				</td>	
			</tr>
			
			<tr>
				
				<td ".$style.">".$_SESSION['lang']['kodevhc']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=kodevhc  style=\"width:154px;\">".$optvhc."</select>
				</td>
			</tr>
			
			<tr>
				<td ".$style.">".$_SESSION['lang']['alokasi']."</td>
				<td>:</td>		
				<td>
					<select class=select2 id=kodeblok  style=\"width:154px;\">".$optalokasi."</select>
				</td>
			</tr>
			
			<tr>
				<td align=center colspan=2></td>
				<td>
					<button class=mybutton onclick=savedt('".$arrdt."')>".$_SESSION['lang']['save']."</button>&nbsp;
					<button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
			
			<tr hidden>
				<td colspan=6>
				methoddt<input  type=text id=methoddt value='insert' class=myinputtext style=\"width:150px;\">
				nourut<input type=text id=nourut readonly class=myinputtext style=\"width:150px;\"></td>
			</tr>
			
		</table></fieldset>";//<input type=hidden id=methodht value='insertdt'>	
	
	// echo"<div id='listdatadetail'></div>";
	
	$frm[0].="
    <fieldset>
            <legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nodok']." </td> 
					 <td  align=center>".$_SESSION['lang']['noakun']."</td>
                    <td  align=center>".$_SESSION['lang']['debet']."</td>
                    <td  align=center>".$_SESSION['lang']['kredit']."</td>
					 <td  align=center>".$_SESSION['lang']['keterangan']." </td> 
					 <td  align=center>".$_SESSION['lang']['kodekegiatan']."</td>
                    <td  align=center>".$_SESSION['lang']['kodeasset']." </td> 
                    <td  align=center>".$_SESSION['lang']['nik']." </td> 
                    <td  align=center>".$_SESSION['lang']['kodecustomer']." </td> 
                    <td  align=center>".$_SESSION['lang']['kodesupplier']." </td> 
                    <td  align=center>".$_SESSION['lang']['kodevhc']." </td> 
                    <td  align=center>".$_SESSION['lang']['alokasi']." </td> 
                    <td  align=center>".$_SESSION['lang']['action']." </td> 
                </tr>  
            </thead>
             <tbody id=listdatadt> 
             </tbody>
             </table>
	</fieldset>";

$frm[1].="<fieldset style='float:left'>
		<legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
				<td>
					<select class=select2 id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload_file' class=mybutton>
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
				<th align='center'>".$_SESSION['lang']['nourut']."</th>
				<th align='center'>".$_SESSION['lang']['tipedokumen']."</th>
				<th align='center'>".$_SESSION['lang']['kriteria']."</th>
				<th align='center'>".$_SESSION['lang']['namafile']."</th>
				<th align='center'>".$_SESSION['lang']['ukurandokumen']."</th>
				<th align='center' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";	
	
	
$hfrm[0]=strtoupper($_SESSION['lang']['transaksi']);
$hfrm[1]=strtoupper($_SESSION['lang']['file']);
drawTab('FRM',$hfrm,$frm,100,'auto');   
CLOSE_BOX();
echo"</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>
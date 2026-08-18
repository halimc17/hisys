<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
require_once('lib/zSelect2.php');
?>
 
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<script language=javascript src='js/pmn_tbsall.js?v=<?php echo time(); ?>'></script>

<?php
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikKar=makeOption($dbname,'datakaryawan','karyawanid,nik');

$optkar=$optkardibuat=$optsupplier=$optunit=$optjenis=$optUnitHutang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optjenissch=$optposting="<option value=''>".$_SESSION['lang']['all']."</option>";

$whrunit=getOrgDetail(2);

$str = "select * from ".$dbname.".datakaryawan where (lokasitugas like '%HO' OR lokasitugas like '%RO%' and lokasitugas in (".$whrunit.")) or karyawanid IN (SELECT karyawanid FROM {$dbname}.setup_approval WHERE kodeunit = '{$_SESSION['empl']['lokasitugas']}')";
$res=fetchdata($str);
foreach($res as $bar){
	$selected='';
	if($_SESSION['standard']['userid']==$bar['karyawanid']){
		$selected='selected';
	}
	@$optkar.="<option value='".$bar['karyawanid']."'>[".$bar['nik']."] ".$bar['namakaryawan']."</option>";
}

	@$optkardibuat.="<option value='".$_SESSION['standard']['userid']."' ".$selected.">[".$nikKar[$_SESSION['standard']['userid']]."] ".$nmKar[$_SESSION['standard']['userid']]."</option>";

$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');


$unit=$_SESSION['empl']['lokasitugas'];
$tipeunit=$_SESSION['empl']['tipelokasitugas'];

if ($tipeunit == 'HOLDING' || $tipe == 'KANWIL'){
	$whr = " ";
}else{
	$whr = " AND kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$str = "select * from ".$dbname.".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
$res=fetchdata($str);
foreach($res as $bar){ 
	@$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
}
 
$arrjnstbs = array('EXT' => 'TBS Eksternal', 'AFI' => 'TBS Afiliasi', 'INT' => 'TBS Internal');
foreach($arrjnstbs as $key=>$val){
	$optjenis.="<option value='".$key."'>".$val."</option>";
	$optjenissch.="<option value='".$key."'>".$val."</option>";
}

// echo $optjenis;
$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL', 'HOLDING')  order by namaorganisasi asc";
$res=fetchdata($str);
foreach($res as $bar){ 
	@$optUnitHutang.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


$str = "select distinct(supplier) as supplier from ".$dbname.".pmn_tbs ";
$res=fetchdata($str);
foreach($res as $bar){ 
	@$optsupplier.="<option value='".$bar['supplier']."'>".$nmsupplier[$bar['supplier']]."</option>";
}

@$optposting.="<option value='0'>".$_SESSION['lang']['belumposting']."</option>";
@$optposting.="<option value='1'>".$_SESSION['lang']['posting']."</option>";


?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('pmn_tbsall').'</span>');
echo"<table>
	<tr valign=middle>
		<td align=center style='width:70px;cursor:pointer;'  onclick=newdata()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
		</td>
		<td>
			<fieldset><legend>".$_SESSION['lang']['find']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>		
					<td>
						<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
						s/d
						<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>			
					</td>
			
					<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>		
					<td>
						<select id=jenissch  style=\"width:155px;\">".$optjenissch."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>		
					<td>
						<select id=unitsch  style=\"width:150px;\">'".$optunit."'</select>
					</td>
				
					<td>".$_SESSION['lang']['supplier']."</td>
					<td>:</td>		
					<td>
						<select id=suppliersch  style=\"width:157px;\">'".$optsupplier."'</select>
						<img id=suppliersch onclick=z.elSearch('suppliersch',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
					</td>
					
					<td>".$_SESSION['lang']['posting']."</td>
					<td>:</td>		
					<td>
						<select id=postingsch  style=\"width:155px;\">'".$optposting."'</select>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>"; 
CLOSE_BOX();
echo "</div>";//tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
echo"<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable  width=100%>
		<caption style='text-align:right'>
			<button class='button verify' onclick=\"loaddata(0,'excel')\">Excel</button>
		</caption>
		<thead>
		<tr class=rowheader>
			<td  align=center>".$_SESSION['lang']['nourut']."</td>
			<td  align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td  align=center>Jenis</td>
			<td  align=center>".$_SESSION['lang']['tanggal']."</td>
			<td  align=center>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['pabrik']."</td>
			<td  align=center>".$_SESSION['lang']['supplier']."</td>
			<td  align=center hidden>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['induk']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
			<td  align=center>".$_SESSION['lang']['berat']."</td>
			<td  align=center>".$_SESSION['lang']['potongan']."</td>
			<td  align=center>".$_SESSION['lang']['netto']."</td>
			<td  align=center>".$_SESSION['lang']['rpkg']."</td>
			<td  align=center>".$_SESSION['lang']['total']."</td>
			<td  align=center>".$_SESSION['lang']['ppn']."</td>
			<td  align=center>".$_SESSION['lang']['pph']."</td>
			<td  align=center>".$_SESSION['lang']['grnd_total']."</td>
			<td  align=center>".$_SESSION['lang']['keterangan']."</td>
			<td  align=center>".$_SESSION['lang']['updateby']."</td>
			<td  align=center colspan=6>".$_SESSION['lang']['action']."</td>    
			<td  align=center>".$_SESSION['lang']['info']."</td>    
		</tr>  
		</thead>
        <tbody id=contain><script>loaddata(0)</script></tbody>
		<tfoot id=footData></tfoot>
	</table>
</fieldset>";//<td  align=center>".$_SESSION['lang']['status']."</td>
CLOSE_BOX();
echo "</div>";//tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->
echo "<div id=header style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['header'].'</span><br>');
echo "<fieldset style=float:left>
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>		
		<td>
			<input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:150px;\">
		</td>		
		
		<td style='padding-left:10px;'>Jenis</td>
		<td>:</td>		
		<td>
			<select class='select2' style='width:155px' id='jenisx' onchange=\"getVendor()\">".$optjenis."</select>
		</td>

		<td style='padding-left:10px;'>".$_SESSION['lang']['supplier']."</td>
		<td>:</td>		
		<td>
			<select class='select2' style='width:155px' id='divisi' onchange=\"getNokontrak()\"></select>
		</td>

		<td style='padding-left:10px;'>".$_SESSION['lang']['afiliasi']."</td>
		<td>:</td>		
		<td><input type=text id=noafiliasi size=20 disabled class=myinputtext style=\"width:150px;\"></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>		
		<td>
			<select class='select2' style='width:155px' id='unit' onchange=\"getVendor()\">".$optunit."</select>
		</td>
		
		<td style='padding-left:10px;'>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['hutang']."</td>
		<td>:</td>		
		<td>
			<select class='select2' style='width:155px' id='unithutang'>".$optUnitHutang."</select>
		</td>

		<td style='padding-left:10px;'>".$_SESSION['lang']['kontrak']."</td>
		<td>:</td>	
		<td>
			<select class='select2' style='width:155px' id='nokontrak'></select>
		</td>
		
		<td valign=top rowspan=4 style='padding-left:10px;'>".$_SESSION['lang']['keterangan']."</td> 
		<td valign=top rowspan=4>:</td>
		<td rowspan=4 valign=top>
			<textarea rows='2' id=keteranganht type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:136px;\"></textarea>
		</td>
	</tr>

	<tr>
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dokumen']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal  placeholder='Tanggal dokumen' name=tanggal name=tanggal  readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:150px;/>
		</td>
	
		<td style='padding-left:10px;'>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tbs']."</td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtext placeholder='dari' id=tanggaltbs1 name=tanggaltbs1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
			s/d <input type=text class=myinputtext  placeholder='sampai' id=tanggaltbs2 name=tanggaltbs2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>
		</td>
		
		<td style='padding-left:10px;'>".$_SESSION['lang']['diperiksa']."</td>
		<td>:</td>		
		<td>
			<select class='select2' style='width:155px' id='diperiksa'>".$optkar."</select>
		</td>
		
		<td></td>
		<td></td>		
		<td></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['ppn']."</td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=persenppn name=persenppn onkeypress=\"return isNumberKey(event);\" maxlength=5 style=width:150px; placeholder=0 /></td>
	
		<td style='padding-left:10px;'>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['pph']."</td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtextnumber id=persenpph name=persenpph onkeypress=\"return isNumberKey(event);\" maxlength=5 style=width:150px; placeholder=0 />
		</td>

		<td style='padding-left:10px;'>".$_SESSION['lang']['disetujui']."</td>
		<td>:</td>		
		<td>
			<select class='select2' style='width:155px' id='disetujui'>".$optkar."</select>
		</td>

		<td style='padding-left:10px;' hidden>".$_SESSION['lang']['dibuat']."</td>
		<td hidden>:</td>		
		<td hidden>
			<select class='select2' style='width:155px' id='dibuat'>".$optkardibuat."</select>
		</td>

		<td></td>
		<td></td>		
		<td></td>
	</tr>
	
	<tr>
		<td align=center colspan=9><button  id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
	</tr>
	</table>
</fieldset>";//<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>
CLOSE_BOX();
echo"</div>";

$border='0';
echo "<div id=detailkud style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
echo "<div style=text-align:right><button class=mybutton title='Cetak Excel' onclick=\"exceldtkud();\">Cetak Excel</button></div>";
	// echo "<fieldset style='width:1750px;height:200px;'>";
	echo "<fieldset style='width:1750px;'>";
            echo "<legend><b>".$_SESSION['lang']['list']."</b></legend>
            <table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
				<tr class=rowheader>
					 <td  align=center>".$_SESSION['lang']['nourut']."<br>(A)</td>
					 <td  align=center>".$_SESSION['lang']['noTiket']."<br>(B)</td>
					 <td  align=center>".$_SESSION['lang']['kodevhc']."<br>(C)</td>
					 <td  align=center>".$_SESSION['lang']['supir']."<br>(D)</td>
					 <td  align=center>".$_SESSION['lang']['supplier']."<br>(E)</td>
					 <td  align=center>".$_SESSION['lang']['tanggal']."<br>PKS<br>(F)</td>
					 <td  align=center>".$_SESSION['lang']['tanggal']."<br>SPB<br>(G)</td>
					 
					 
					 <td  align=center>".$_SESSION['lang']['berat']." I<br>(H)</td>
					 <td  align=center>".$_SESSION['lang']['berat']." II<br>(I)</td>
					 <td  align=center>".$_SESSION['lang']['berat']." TBS<br>(J=I-H)</td>
					 <td  align=center>".$_SESSION['lang']['potongan']."<br>(K)</td>
					 <td  align=center>".$_SESSION['lang']['netto']."<br>(L=J-K)</td> 
					 
					 <td  align=center>".$_SESSION['lang']['jjg']."<br>(M)</td> 
					 <td  align=center>".$_SESSION['lang']['sample']."<br>(N)</td> 
					 <td  align=center>".$_SESSION['lang']['bjr']."<br>(O)</td> 

					 <td  align=center>".$_SESSION['lang']['netto']." Brondolan<br>(P)</td>
					 
					  <td  align=center>".$_SESSION['lang']['nospb']."<br>(Q)</td>
					 <td  align=center>".$_SESSION['lang']['blok']."<br>(R)</td>   
					 <td  align=center>".$_SESSION['lang']['nama']."<br>(S)</td>   
					 <td  align=center>".$_SESSION['lang']['tahuntanam']."<br>(T)</td>   
					 <td  align=center>".$_SESSION['lang']['harga']."<br>(U)</td>   
					 <td  align=center>".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['aktual']."<br>(V=L*u)</td>  
					 
					 <td hidden align=center>".$_SESSION['lang']['potongan']." %<br>(".$_SESSION['lang']['aktual'].")<br>(V=K/J)</td>   
					 <td hidden align=center>".$_SESSION['lang']['adjust']." %<br>(Setup)<br>(W)</td>   
					 <td hidden align=center>".$_SESSION['lang']['adjust']." %<br>(Perhitungan)<br>(X=round(if(V>W,V-W,0)))</td>   
					 <td hidden align=center>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Y=round(X*L))</td>   
					 <td hidden align=center>".$_SESSION['lang']['netto']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Z=J-Y)</td>   
					 <td hidden align=center>".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AA=T*Y)</td>   
					 <td hidden align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AB=U+AA)</td>  
					 <td  align=center>*</td> 		

					 
					 
				</tr>
               
            </thead>
             <tbody id=listdatadtkud> 
             </tbody>
             </table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();	

$border='0';
echo "<div id=detailafiliasi style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
echo "<div style=text-align:right><button class=mybutton title='Cetak Excel' onclick=\"exceldtafi();\">Cetak Excel</button></div>";
  echo "<div style=height:300px class=table-scroll>";
            echo "
            <table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
				<tr class=rowheader>
					 <th  align=center>".$_SESSION['lang']['nourut']."<br></th>
					 <th  align=center>".$_SESSION['lang']['noTiket']."<br>(A)</th>
					 <th  align=center>Kelas ".$_SESSION['lang']['buah']."<br>(B)</th>
					 <th  align=center>".$_SESSION['lang']['kodevhc']."<br>(C)</th>
					 <th  align=center>".$_SESSION['lang']['supir']."<br>(D)</th>
					 <th  align=center>".$_SESSION['lang']['supplier']."<br>(E)</th>
					 <th  align=center>".$_SESSION['lang']['tanggal']."<br>PKS<br>(F)</th>
					 <th  align=center>".$_SESSION['lang']['tanggal']."<br>SPB<br>(G)</th>
					 
					 <th  align=center>".$_SESSION['lang']['berat']." I<br>(H)</th>
					 <th  align=center>".$_SESSION['lang']['berat']." II<br>(I)</th>
					 <th  align=center>".$_SESSION['lang']['berat']." TBS<br>(J=I-H)</th>
					 <th  align=center>".$_SESSION['lang']['potongan']."<br>(K)</th>
					 <th  align=center>".$_SESSION['lang']['netto']."<br>(L=J-K)</th> 
					 
					 <th  align=center>".$_SESSION['lang']['jjg']."<br>(M)</th> 
					 <th  align=center>".$_SESSION['lang']['sample']."<br>(N)</th> 
					 <th  align=center>".$_SESSION['lang']['bjr']."<br>(O)</th> 
					 
					  <th  align=center>".$_SESSION['lang']['nospb']."<br>(P)</th>
					 <th  align=center>".$_SESSION['lang']['blok']."<br>(Q)</th>   
					 <th  align=center>".$_SESSION['lang']['nama']."<br>(R)</th>   
					 <th  align=center>".$_SESSION['lang']['tahuntanam']."<br>(S)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['harga']."<br>(T)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['aktual']."<br>(U=L*T)</th>  
					 
					 <th  align=center hidden>".$_SESSION['lang']['potongan']." %<br>(".$_SESSION['lang']['aktual'].")<br>(V=K/J)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['adjust']." %<br>(Setup)<br>(W)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['adjust']." %<br>(Perhitungan)<br>(X=round(if(V>W,V-W,0)))</th>   
					 <th  align=center hidden>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Y=round(X*L))</th>   
					 <th  align=center hidden>".$_SESSION['lang']['netto']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Z=J-Y)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AA=T*Y)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AB=U+AA)</th>  
					 <th  align=center>*</th> 		

					 
					 
				</tr>
               
            </thead>
             <tbody id=listdatadtafiliasi> 
             </tbody>
             </table></div>";
CLOSE_BOX();
echo"</div>";
echo close_body();

$border='0';
echo "<div id=detailexternal style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
echo "<div style=text-align:right><!--<button class=mybutton title='Cetak Excel' onclick=\"exceldtext();\">Cetak Excel</button>--></div>";
            echo "<div style=height:300px class=table-scroll>";
             echo "<table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
				<tr class=rowheader>
					 <th  align=center>".$_SESSION['lang']['nourut']."<br></th>
					 <th  align=center>".$_SESSION['lang']['noTiket']."<br>(A)</th>
					 <th  align=center>Kelas ".$_SESSION['lang']['buah']."<br>(B)</th>
					 <th  align=center>".$_SESSION['lang']['kodevhc']."<br>(C)</th>
					 <th  align=center>".$_SESSION['lang']['supir']."<br>(D)</th>
					 <th  align=center>".$_SESSION['lang']['supplier']."<br>(E)</th>
					 <th  align=center>".$_SESSION['lang']['tanggal']."<br>PKS<br>(F)</th>
					 <th  align=center>".$_SESSION['lang']['tanggal']."<br>SPB<br>(G)</th>
					 
					 <th  align=center>".$_SESSION['lang']['berat']." I<br>(H)</th>
					 <th  align=center>".$_SESSION['lang']['berat']." II<br>(I)</th>
					 <th  align=center>".$_SESSION['lang']['berat']." TBS<br>(J=I-H)</th>
					 <th  align=center>".$_SESSION['lang']['potongan']."<br>(K)</th>
					 <th  align=center>".$_SESSION['lang']['netto']."<br>(L=J-K)</th> 
					 
					 <th  align=center>".$_SESSION['lang']['jjg']."<br>(M)</th> 
					 <th  align=center>".$_SESSION['lang']['sample']."<br>(N)</th> 
					 <th  align=center>".$_SESSION['lang']['bjr']."<br>(O)</th> 
					 
					  <th  align=center>".$_SESSION['lang']['nospb']."<br>(P)</th>
					 <th  align=center>".$_SESSION['lang']['blok']."<br>(Q)</th>   
					 <th  align=center>".$_SESSION['lang']['nama']."<br>(R)</th>   
					 <th  align=center>".$_SESSION['lang']['tahuntanam']."<br>(S)</th>   
					 <th  align=center >".$_SESSION['lang']['harga']."<br>(T)</th>   
					 <th  align=center >".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['aktual']."<br>(U=L*T)</th>  
					 
					 <th  align=center hidden>".$_SESSION['lang']['potongan']." %<br>(".$_SESSION['lang']['aktual'].")<br>(V=K/J)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['adjust']." %<br>(Setup)<br>(W)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['adjust']." %<br>(Perhitungan)<br>(X=round(if(V>W,V-W,0)))</th>   
					 <th  align=center hidden>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Y=round(X*L))</th>   
					 <th  align=center hidden>".$_SESSION['lang']['netto']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Z=J-Y)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AA=T*Y)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AB=U+AA)</th>  
					 <th  align=center>*</th> 		

					 
					 
				</tr>
               
            </thead>
             <tbody id=listdatadtexternal> 
             </tbody>
             </table></div>";

CLOSE_BOX();
echo"</div>";
echo close_body();

$border='0';
echo "<div id=detailinternal style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['detail'].'</span>');
echo "<div style=text-align:right><button class=mybutton title='Cetak Excel' onclick=\"exceldtint();\">Cetak Excel</button></div>";
            echo "<div style=height:300px class=table-scroll>";
             echo "<table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
				<tr class=rowheader>
					 <th  align=center>".$_SESSION['lang']['nourut']."<br></th>
					 <th  align=center>".$_SESSION['lang']['noTiket']."<br>(A)</th>
					 <th  align=center>Kelas ".$_SESSION['lang']['buah']."<br>(B)</th>
					 <th  align=center>".$_SESSION['lang']['kodevhc']."<br>(C)</th>
					 <th  align=center>".$_SESSION['lang']['supir']."<br>(D)</th>
					 <th  align=center>".$_SESSION['lang']['supplier']."<br>(E)</th>
					 <th  align=center>".$_SESSION['lang']['tanggal']."<br>PKS<br>(F)</th>
					 <th  align=center>".$_SESSION['lang']['tanggal']."<br>SPB<br>(G)</th>
					 
					 <th  align=center>".$_SESSION['lang']['berat']." I<br>(H)</th>
					 <th  align=center>".$_SESSION['lang']['berat']." II<br>(I)</th>
					 <th  align=center>".$_SESSION['lang']['berat']." TBS<br>(J=I-H)</th>
					 <th  align=center>".$_SESSION['lang']['potongan']."<br>(K)</th>
					 <th  align=center>".$_SESSION['lang']['netto']."<br>(L=J-K)</th> 
					 
					 <th  align=center>".$_SESSION['lang']['jjg']."<br>(M)</th> 
					 <th  align=center>".$_SESSION['lang']['sample']."<br>(N)</th> 
					 <th  align=center>".$_SESSION['lang']['bjr']."<br>(O)</th> 
					 
					  <th  align=center>".$_SESSION['lang']['nospb']."<br>(P)</th>
					 <th  align=center>".$_SESSION['lang']['blok']."<br>(Q)</th>   
					 <th  align=center>".$_SESSION['lang']['nama']."<br>(R)</th>   
					 <th  align=center>".$_SESSION['lang']['tahuntanam']."<br>(S)</th>   
					 <th  align=center >".$_SESSION['lang']['harga']."<br>(T)</th>   
					 <th  align=center >".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['aktual']."<br>(U=L*T)</th>  
					 
					 <th  align=center hidden>".$_SESSION['lang']['potongan']." %<br>(".$_SESSION['lang']['aktual'].")<br>(V=K/J)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['adjust']." %<br>(Setup)<br>(W)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['adjust']." %<br>(Perhitungan)<br>(X=round(if(V>W,V-W,0)))</th>   
					 <th  align=center hidden>".$_SESSION['lang']['total']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Y=round(X*L))</th>   
					 <th  align=center hidden>".$_SESSION['lang']['netto']."<br>".$_SESSION['lang']['adjust']."<br>".$_SESSION['lang']['kg']."<br>(Z=J-Y)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AA=T*Y)</th>   
					 <th  align=center hidden>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['rp']."<br>".$_SESSION['lang']['adjust']."<br>(AB=U+AA)</th>  
					 <th  align=center>*</th> 		

					 
					 
				</tr>
               
            </thead>
             <tbody id=listdatadtinternal> 
             </tbody>
             </table></div>";

CLOSE_BOX();
echo"</div>";
?>
<script>
	getSelect2();
</script>
<?php
echo close_body();
?>
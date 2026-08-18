<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_noninventory.php').'</span>');
?>

<link rel="stylesheet" type="text/css" href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script type="text/javascript" src="js/log_noninventory.js?v=<?php echo time(); ?>" /></script>
<script type="text/javascript" src="js/log_link.js?v=1.4" /></script>
<script language=javascript src='js/vhc_detailkmhm.js'></script>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?php
echo"<div id='action_list'>
	<table>
		<tr valign=moiddle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayforminput()>
		<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	<td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	</td>
			<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend>
				".$_SESSION['lang']['notransaksi']." : <input type=text id=scnotransaksi size=25 maxlength=30 class=myinputtext>
				".$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=sctanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
				".$_SESSION['lang']['nopo']." : <input type=text id=crnopo size=25 maxlength=30 class=myinputtext>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				</fieldset>
			</td>
		</tr>
	</table>
</div>";

CLOSE_BOX();

OPEN_BOX();
echo"<div id='listdata' class='table-scroll' style=height:65vh>
	<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
		<thead>
			<tr style='text-align:center;font-weight:bold'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['notransaksi']."</th>
				<th>".$_SESSION['lang']['tipe']."</th>
				<th>".$_SESSION['lang']['perusahaan']."</th>
				<th>".$_SESSION['lang']['unit']."</th>
				<th>".$_SESSION['lang']['tanggal']."</th>
				<th>".$_SESSION['lang']['nopo']."</th>
				<th>".$_SESSION['lang']['namasupplier']."</th>
				<th>".$_SESSION['lang']['termin']."</th>
				<th>".$_SESSION['lang']['dibuat']."</th>
				<th>".$_SESSION['lang']['approval_status']."</th> 
				<th>".$_SESSION['lang']['posting']."</th>
				<th align='center' colspan=5>Action</th>
			</tr>
			</thead>
			<tbody id='contain'>
			<script>loaddata(0);</script>
			</tbody>
			<tfoot id=footer>
			</tfoot>
		</table>
</div>";

echo"<div id=forminput style='display:none'>";
	
	## GET UNIT
	$optunit='';
	$arrorgdet = getOrgDetail(1);
	$no=0;
	foreach($arrorgdet as $key=>$val){
		$no++;
		if($no==1){
			$unitkerja = $key;
		}
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";	
	}

	$OPTKAR = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
	$OPTNIK = makeOption($dbname,'datakaryawan','karyawanid,nik');
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='APVGRNI'";
	$bar=fetchdata($str)[0];
	$nama=explode(',',$bar['nilai']);
	foreach($nama as $list => $isi){
		$optkaryawan2.="<option value=".$isi.">".$OPTKAR[$isi]." - ".$OPTNIK[$isi]."</option>";
	}
	## GET KARYAWAN
	$str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unitkerja."' or karyawanid='".$_SESSION['standard']['userid']."' order by namakaryawan";
	$res=fetchdata($str);
	foreach($res as $val){
		$optkaryawan2.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." - ".$val['nik']."</option>";
	}
	
	## GET KARYAWAN
	$optkaryawan="";
	$str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unitkerja."' or karyawanid='".$_SESSION['standard']['userid']."' order by namakaryawan";
	$res=fetchdata($str);
	foreach($res as $val){
		if($val['karyawanid']==$_SESSION['standard']['userid']){
			$optkaryawan.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']." - ".$val['nik']."</option>";
		}
		$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." - ".$val['nik']."</option>";
	}
	
		## GET SURAT JALAN
		$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unitkerja."'");
		$optsj="";
		$optsj.="<option value=''></option>";
		// $str="select nosj from ".$dbname.".log_suratjalanht where kodept='".$optpt[$unitkerja]."' and nosj not in (select nosj from ".$dbname.".log_transaksiht where kodept='".$optpt[$unitkerja]."') and posting='1' order by nosj";
		// $res=fetchdata($str);
		// foreach($res as $val){
		// 	$optsj.="<option value='".$val['nosj']."'>".$val['nosj']."</option>";
		// }
	
		$str="select b.* from ".$dbname.".log_suratjalanht a left join ".$dbname.".log_suratjalandt b on a.nosj=b.nosj left join ".$dbname.".log_poht c on b.nopo=c.nopo where a.kodept='".$optpt[$unitkerja]."' 
		and a.franco like '".$unitkerja."%' and a.posting='1' and c.tipepo != 'PO'  group by b.nosj,b.nopo,b.kodebarang";
		$res=fetchdata($str);
		foreach($res as $valxx){
	
			$strx="select nopo,jumlahpesan, jmlhstlhclose, kodebarang, satuan from ".$dbname.".log_podt where nopo='".$valxx['nopo']."' and kodebarang='".$valxx['kodebarang']."'";   
			$resx=fetchdata($strx);
			$counthasil = 0;
			foreach($resx as $valx){
				$strxx="select kodebarang, sum(jumlah) as jumlahterima, satuan from ".$dbname.".log_transaksidt where nopo='".$valx['nopo']."' and kodebarang='".$valx['kodebarang']."'";
				$resxx=fetchdata($strxx);
				$jlhterima = ($resxx[0]['jumlahterima']==''?0:$resxx[0]['jumlahterima']);
		
				if($valx['satuan']!=$resxx[0]['satuan']){ // Satuan PO tidak sama dengan Transaksi?
				  //konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
				  $str1="select jumlah from ".$dbname.".log_5stkonversi 
						 where darisatuan='".$resxx[0]['satuan']."' and satuankonversi='".$valx['satuan']."'
						 and kodebarang='".$resxx[0]['kodebarang']."'";
				  $res3=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				  $res3->setFetchMode(PDO::FETCH_OBJ);
				  while($bar2=$res3->fetch())
				  {
					  $valx['jumlahpesan']=round(($valx['jumlahpesan']/$bar2->jumlah),6);//mengkonversi satuan
				  }	   
		
				}
				// exit("warning : ".$valx['jumlahpesan']." - ".$jlhterima." - ".$valx['jmlhstlhclose']." ");
				$hasil = $valx['jumlahpesan'] - $jlhterima - $valx['jmlhstlhclose'];
				if($hasil > 0){
					// $counthasil++;
					$array_nopoSJ[$valx['nopo']]=$valx['nopo'];
				}
			}
			
		}
		// Mendapatkan array kunci
		$keys = array_keys($array_nopoSJ);
		// Mengubah array kunci menjadi string yang dapat digunakan dalam klausa IN
		$inClause = "'" . implode("', '", $keys) . "'";
	
		$str="select nosj from ".$dbname.".log_suratjalandt where nopo in (".$inClause.") group by nosj";
		$res=fetchdata($str);
		foreach($res as $val){
			$optsj.="<option value='".$val['nosj']."'>".$val['nosj']."</option>";
		}
	


    echo"<div>
		<fieldset><legend>".$_SESSION['lang']['header']."</legend>
		<table cellpadding=2>
			<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>
					<input type='text' id='notransaksi' class='myinputtext' disabled='disabled' style='width:145px;' /> <font color=red>*
				</td>
			
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>
					<select class=select2 id='unit' onchange=\"getpenerima()\" style='width:150px;'>".$optunit."</select>
					<img id='imgunit' onclick=z.elSearch('unit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['penerima']."</td>
				<td>:</td>
				<td>
					<select class=select2 id='penerima' style='width:150px;'>".$optkaryawan."</select>
					<img id='imgpenerima' onclick=z.elSearch('penerima',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
				
				<td>".$_SESSION['lang']['disetujui']."</td>
				<td>:</td>
				<td>
					<select class=select2 id='disetujui' style='width:150px;'>".$optkaryawan2."</select>
					<img id='imgdisetujui' onclick=z.elSearch('disetujui',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtext' id='tanggal' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' />
				</td>
			
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>
					<input type='hidden' id='showtermin' value='0'>
					<input type=text class=myinputtext style='width:150px' id=nopo disabled>
					<img id='imgnopo' src='images/onebit_02.png' style='position:relative;top:3px;padding-right:5px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"formcarinopo('&nbsp;&nbsp;&nbsp;Form Pencarian','<fieldset width=100%><table cellpadding=3><tr><td>No. PO/SO</td><td>:</td><td><input type=text class=myinputtext id=scnopo onkeypress=enterkey(event,carinopo)></td></tr><tr><td colspan=2></td><td><button class=mybutton onclick=carinopo()>Find</button></td></tr></table><br><div id=popuplistpo></div>',event)\";><br></fieldset>
				</td>
			</tr>
			<tr>
				
			<td>".$_SESSION['lang']['suratjalan']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='nosj' style='width:150px;'>".$optsj."</select>
				<img id='nosj' onclick=z.elSearch('nosj',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>


			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>
				&nbsp;
			</td>
		</tr>
			<tr style='display:none'>
				<td class='tdtermin' style='display:none'>".$_SESSION['lang']['termin']."</td>
				<td class='tdtermin' style='display:none'>:</td>
				<td class='tdtermin' style='display:none'>
					<select id='termin'></select>
				</td>
			</tr>
		</table>
		</fieldset>
		
		<fieldset id='listitempo' style='display:none'><legend>".$_SESSION['lang']['daftarbarang']."</legend>
			<div id='databarang'></div>
		</fieldset>
    </div>";
CLOSE_BOX();

echo close_body(); 
?>
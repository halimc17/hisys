<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zForm.php');
echo open_body();
?>
<script language="javascript1.2">
notiftextshort="<?php echo $_SESSION['lang']['notiftextshort']; ?>";
notifandayakin="<?php echo $_SESSION['lang']['notifandayakin']; ?>";
notifdatainconsistent="<?php echo $_SESSION['lang']['notifdatainconsistent']; ?>";
datatersimpan="<?php echo $_SESSION['lang']['datatersimpan']; ?>";
notifdeleteingdata="<?php echo $_SESSION['lang']['notifdeleteingdata']; ?>";
</script>
<script language=javascript1.2 src='js/asset.js?v=<?php echo time()?>'></script>
<?
include('master_mainMenu.php');
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
    $inorg[$kei]=$kei;
}
//limit/page
$limit = 20;
$page = 0;
if (isset($_POST['page'])) {
	$page = $_POST['page'];
	if ($page < 0)
		$page = 0;
}
$offset = $page * $limit;
//===========================
//===========================
$folder="fileupload/qrcodeasset/";
$str = "select a.*
	from ".$dbname.".sdm_daftarasset a
	where  kodeorg in ('".implode("','",$inorg)."')";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
$jlhbzrs = $numrows;
//===================================================

$optOrg=$optAss="<option value=''>".$_SESSION['lang']['all']."</option>";

//ambil option organisasi
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
      tipe in('HOLDING','KEBUN','KANWIL','PABRIK','RND','TC','BULKING')
      and kodeorganisasi in ('".implode("','",$inorg)."') order by namaorganisasi";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
        $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." ".$bar->namaorganisasi."</option>";
}  
//=====================
//tipe asset
$str=" select * from ".$dbname.".sdm_5tipeasset order by namatipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if($_SESSION['language']=='EN'){
		$optAss.="<option value='".$bar->kodetipe."'>".$bar->namatipe1."</option>";
    }else{    
		$optAss.="<option value='".$bar->kodetipe."'>".$bar->namatipe."</option>";
    }
}

//jenis biaya
$optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arjb = getEnum($dbname, 'sdm_daftarasset', 'jenis_biaya');
foreach ($arjb as $kei => $fal) {
	// if ((substr($_SESSION['empl']['lokasitugas'],2,2)=='HO')&&($fal!=3)){
		// continue;	
	// }

	// if ((substr($_SESSION['empl']['lokasitugas'],2,2)!='HO')&&($fal==3)){
		// continue;
	// }

	if ($fal==1){
		$capt="Biaya Langsung";
	}
	if ($fal==2){
		$capt="Biaya Tidak Langsung";
	}
	if ($fal==3){
		$capt="Operasi";
	}

    $optjb.="<option value='" . $kei . "'>" . $capt . "</option>";
}

//=========================================
//awal penyusutan
$optper="<option value=''></option>";
$optper2="<option value='0000-00'></option>";
for($x=-3;$x<=250;$x++){
	$d=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$da=date('Y-m',$d);
	$di=date('m-Y',$d);
	$optper.="<option value='".$da."'>".$di."</option>";
	$optper2.="<option value='".$da."'>".$di."</option>";
}
//===========================

//option status
$optStat="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStat.="<option value='1'>".$_SESSION['lang']['aktif']."</option>";
// $optStat.="<option value='2'>".$_SESSION['lang']['rusak']."</option>";
// $optStat.="<option value='3'>".$_SESSION['lang']['hilang']."</option>";
$optStat.="<option value='0'>".$_SESSION['lang']['tidakaktif']."</option>";

// $str="select * from ".$dbname.".keu_5jenisdisposalasset order by keterangan";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
    // $optStat.="<option value='".$bar['id']."'>".$bar['keterangan']."</option>";
    
// }

$orgOption="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)='4'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $orgOption.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
    
}

// $optTipeLokasiAsset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $str="select * from ".$dbname.".keu_5tipelokasiasset where tipelokasi='HO' order by namalokasi";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
//     $optTipeLokasiAsset.="<option value='".$bar['kodelokasi']."'>".$bar['namalokasi']."</option>";
    
// }

//option leasing
$optLeas="<option value='0'>Not Leasing</option>";
$optLeas.="<option value='1'>Leasing</option>";
$optLeas.="<option value='2'>Ex-Leasing</option>";
$kamusleasing[0]='Not Leasing';
$kamusleasing[1]='Leasing';

//===========================

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['daftarasset']).'</span>');
echo"<table border=0>
     <tr valign=moiddle>
		<td align=center style='cursor:pointer;' onclick=displayFormInput()>
			<img class=delliconBig  src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
		<td>&nbsp;</td>
        <td align=center style='cursor:pointer;' onclick=displayList()>
        	<img  class=delliconBig  src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
        <td>
		<fieldset>
			<legend>".$_SESSION['lang']['find']."</legend>";
		echo "<table border=0>
			<tr>
				<td>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td>:</td>
				<td><select style=width:153px id=kodeorgsch>".$optOrg."</select></td>

				
				<td>".$_SESSION['lang']['kodeasset']."</td>
				<td>:</td>
				<td><input type=text id=kodeasetsch maxlength=64 class=myinputtext style=width:153px></td>
			
				
				<td>".$_SESSION['lang']['posisiasset']."</td>
				<td>:</td>
				<td>
					<select  style=width:153px id=posisiassetsch onchange='changetipelokasisch()'>".$orgOption."</select>
				</td>
				
			</tr>
	        <tr>
				<td>".$_SESSION['lang']['namakelompok']."</td>
				<td>:</td>
				<td><select  style=width:153px id=tipesch onchange=getSubsch()>".$optAss."</select></td>
			
				<td>".$_SESSION['lang']['kodeasset']." ".$_SESSION['lang']['lama']."</td>
				<td>:</td>
				<td><input type=text id=kodeasetlamasch maxlength=64 class=myinputtext style=width:153px></td>


				
				
				<td>Tipe Lokasi Asset</td>
				<td>:</td>
				<td>
					<select  style=width:153px id=tipelokasiassetsch></select>
				</td>
			</tr>
			<tr>
			
				<td>".$_SESSION['lang']['subtipeasset']."</td>
				<td>:</td>
				<td><select style=width:153px id=subsch>".$optSub."</select></td>
			
				<td>".$_SESSION['lang']['namaaset']."</td>
				<td>:</td>
				<td><input type=text id=namaasetsch maxlength=64 class=myinputtext style=width:153px></td>
				
				
				<td>".$_SESSION['lang']['status']."</td>
				<td>:</td>
				<td>
					<select style=width:153px id=statussch>".$optStat."</select>
				</td>

			</tr>
			
			<tr>
				<td>".$_SESSION['lang']['awalpenyusutan']."</td>
				<td>:</td>
				<td>
					<select  style=width:153px id=bulanawalsch>".$optper."</select>
				</td>
				
				<td>".$_SESSION['lang']['project']."</td>
				<td>:</td>
				<td><input type=text id=kodeprojectsch maxlength=64 class=myinputtext style=width:153px></td>
			
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button> 
					<button class=mybutton onclick=displayList()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
			<tr hidden>
				<td hidden>".$_SESSION['lang']['caripadanama']."</td>
				<td hidden>:</td>
				<td hidden><input type=text style=width:153px id=txtsearch size=25 maxlength=30 class=myinputtext></td>
			</tr>
		</table>";
		echo"</fieldset>
		</td>
     </tr>
	 </table> "; 
CLOSE_BOX();


OPEN_BOX('','');
// $dmn="char_length(kodeorganisasi)='4'";
// $orgOption['']=$_SESSION['lang']['pilihdata'];
// $orgOption = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $dmn,'2');
echo "<fieldset id='formInput' style='display:none'>
        <legend>".$_SESSION['lang']['inputaset']."</legend>
    <table border=0>
		<tr>
			<td class='bintang'>".$_SESSION['lang']['kodeorganisasi']."</td><td>:</td><td><select style=width:153px id=kodeorg>".$optOrg."</select>
			<img hidden class='resicon' src=\"images/obl.png\" title='".$_SESSION['lang']['notifobligatory']."'></td></td>
			<td class='bintang'>".$_SESSION['lang']['jenisbiaya']."</td><td>:</td><td><select style=width:153px id=jenisbiaya>".$optjb."</select>
			<img hidden class='resicon' src=\"images/obl.png\" title='".$_SESSION['lang']['notifobligatory']."'>
			<td><input type=hidden id=penambah class=myinputtextnumber  onkeypress=\"return angka_doang(event)\" size=20 /></td>
		</tr>
        <tr>
			<td>".$_SESSION['lang']['kodeasset']."</td><td>:</td>
			<td><input type=text id=kodeaset maxlength=20 class=myinputtext onkeypress=\"return angka_doang(event)\" style=width:150px disabled></td></td>
			<input type=hidden id=pengurang class=myinputtextnumber  onkeypress=\"return angka_doang(event)\" size=20 /></td>
			<td>".$_SESSION['lang']['induk']."</td><td>:</td>
			<td>".makeElement('induk','text','',array('maxlength'=>25,'style'=>'width:150px'))."             
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoInduk class=resicon onclick=cariNoInduk('".$_SESSION['lang']['find']."',event)></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodeasset']." ".$_SESSION['lang']['lama']."</td><td>:</td>
			<td><input type=text id=kodeasetlama maxlength=64 class=myinputtext style=width:150px></td>
			<td>".$_SESSION['lang']['namakelompok']."</td><td>:</td>
			<td><select  style=width:153px id=tipe onchange=getSub()>".$optAss."</select></td>
			
		</tr>
        <tr>
			<td class='bintang'>".$_SESSION['lang']['tanggalperolehan']."</td><td>:</td>
			<td><input type=text class='myinputtext' id='tahunperolehan' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style='width:150px' maxlength='10' onchange='getPrdAwal(this)' readonly/><img hidden class='resicon' src=\"images/obl.png\" title='".$_SESSION['lang']['notifobligatory']."'></td></td>
			<td class='bintang'>".$_SESSION['lang']['subtipeasset']."</td><td>:</td>
			<td><select  style=width:153px id=sub onchange=cek()>".@$optSub."</select><img hidden class='resicon' src=\"images/obl.png\" title='".$_SESSION['lang']['notifobligatory']."'></td>
		</tr>
        <tr>
			<td>".$_SESSION['lang']['hargaperolehan']."</td><td>:</td><td><input type=text value=0 class=myinputtextnumber id=nilaiperolehan onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('nilaiperolehan')\"	style='width:150px' maxlength=15></td>
			</td>
			<td class='bintang'>".$_SESSION['lang']['namaaset']."</td><td>:</td><td><input type=text id=kodebarang onkeypress=\"return false;\" onclick=\"showWindowBarang('Cari Barang',event);\" class=myinputtext style='width:100px' maxlength=11><input type=text id=namaaset maxlength=45 class=myinputtext onkeypress=\"return tanpa_kutip(event)\" style='width:233px'>
			<img hidden class='resicon' src=\"images/obl.png\" title='".$_SESSION['lang']['notifobligatory']."'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['awalpenyusutan']."</td><td>:</td>
			<td><select  style=width:153px id=bulanawal>".$optper."</select></td></td>
			<td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><input type=text class=myinputtext  id=keterangan style=width:337px maxlength=100  onkeypress=\"return tanpa_kutip(event)\"></td>
		</tr>
		<tr>
			<td class='bintang'>".$_SESSION['lang']['status']."</td><td>:</td><td><select style=width:153px id=status>".$optStat."</select><img hidden class='resicon' src=\"images/obl.png\" title='".$_SESSION['lang']['notifobligatory']."'></td>
			<td>".$_SESSION['lang']['jumlahbulanpenyusutan']."</td><td>:</td>
			<td><input type=text value=0 class=myinputtextnumber id=jumlahbulan onkeypress=\"return angka_doang(event);\" size=9 maxlength=3> /
				<input type=text value=0 class=myinputtextnumber id=persendecline onkeypress=\"return angka_doang(event);\" size=5 maxlength=3 > %
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggaldisposal']."</td><td>:</td>
			<td><input type=text class='myinputtext' id='tanggaldisposal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style='width:150px' maxlength='10' disabled /></td></td>
			<td>Ref. ".$_SESSION['lang']['pembayaran']."</td><td>:</td>
			<td>".makeElement('refbayar','text','',array('maxlength'=>25,'style'=>'width:150px'))."</td>
		</tr>
		<tr>
			<td>Leasing</td><td>:</td>
			<td><select style=width:153px id=leasing>".$optLeas."</select></td>
			<td class='bintang'>".$_SESSION['lang']['posisiasset']."</td><td>:</td>
			<td><select  style=width:153px id=posisiasset onchange='changetipelokasi()'>".$orgOption."</select><img hidden class='resicon' src=\"images/obl.png\" title='Posisi Asset' ></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nodokpengadaan']."</td><td>:</td>
			<td>".makeElement('nodokpengadaan','text','',array('maxlength'=>25,'style'=>'width:150px'))."             
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=cariNoGudang('".$_SESSION['lang']['find']."',event)></td>
			<td class='bintang'>Tipe Lokasi Asset</td><td>:</td>
			<td><select style=width:153px id=tipelokasiasset ></select><img hidden class='resicon' src=\"images/obl.png\" title='Tipe Lokasi Asset'></td>
		</tr>
		<tr>
			<td>No Mesin</td><td>:</td><td><input type=text class=myinputtext  id=nomesin style=width:150px   onkeypress=\"return tanpa_kutip(event)\"></td>
			<td>No Rangka</td><td>:</td><td><input type=text class=myinputtext  id=norangka style=width:150px   onkeypress=\"return tanpa_kutip(event)\"></td>
		</tr>
		<tr>
			<td>Tipe Model</td><td>:</td><td><input type=text class=myinputtext  id=tipemodel style=width:150px   onkeypress=\"return tanpa_kutip(event)\"></td>
			<td>Penyusutan Tambahan</td><td>:</td><td><input type=text value=0 class=myinputtextnumber id=penyusutantambahan style=width:150px onkeypress=\"return angka_doang(event);\" size=9></td>
		</tr>
	<tr><td><td><td>
    <input type=hidden value=insert id=method>
    <button class=mybutton onclick=simpanAssetBaru()>".$_SESSION['lang']['save']."</button>
    <button class=mybutton onclick=cancelAsset()>". $_SESSION['lang']['cancel']."</button>
	</table>
	</fieldset>";

echo "<fieldset id='listData'><legend>".$_SESSION['lang']['list']."</legend>
         <div style='height:420px;width:100%;overflow:auto;'>
		 <table class=sortable width=100% border=0 cellspacing=1>
		 <thead>
		   <tr class=rowheader>
			  <td align=center>".$_SESSION['lang']['nourut']."</td>
			  <td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			  <td align=center>".$_SESSION['lang']['posisiasset']."</td>
			  <td align=center>Tipe Lokasi Asset</td>
			  <td align=center>".$_SESSION['lang']['namakelompok']."</td>
			  <td align=center>".$_SESSION['lang']['kodeasset']."</td>
			  <td align=center>".$_SESSION['lang']['kodeasset']." ".$_SESSION['lang']['lama']."</td>
			  <td align=center>".$_SESSION['lang']['namaaset']."</td>
			  <td align=center>".$_SESSION['lang']['tahunperolehan']."</td>
			  <td align=center>".$_SESSION['lang']['status']."</td>
			  <td align=center>".$_SESSION['lang']['hargaperolehan']."</td>
			  <td width=20 align=center>".$_SESSION['lang']['jumlahbulanpenyusutan']."</td>
			  <td align=center>".$_SESSION['lang']['persendecline']."</td>
			  <td align=center>".$_SESSION['lang']['awalpenyusutan']."</td>
			  <td align=center>".$_SESSION['lang']['tanggaldisposal']."</td>
			  <td align=center>No Rangka</td>
			  <td align=center>No Mesin</td>
			  <td align=center>".$_SESSION['lang']['keterangan']."</td>
			  <td align=center>Leasing</td>
			  <td align=center>".$_SESSION['lang']['project']."</td>
			  <!--<td align=center>Tipe Lokasi</td>-->
			  <td align=center>Action</td>
			</tr>
		  </thead>		   
		  <tbody id=containeraset>
		  <script>loadData(0)</script>";
		  
//          if($_SESSION['language']=='EN'){
//              $ads="b.namatipe1 as namatipe";
//          }
//          else{
//             $ads="b.namatipe as namatipe"; 
//          }
//         $str="select a.*,".$ads." from ".$dbname.".sdm_daftarasset a
//               left join  ".$dbname.".sdm_5tipeasset b
//               on a.tipeasset=.b.kodetipe
//                   where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
//                   order by tanggalperolehan desc,awalpenyusutan desc,namatipe asc
//           limit ".$offset.",".$limit;

//         $no=$offset;
//         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//         $res->setFetchMode(PDO::FETCH_OBJ);
//         while($bar=$res->fetch())
//         {


// 		        $whr="id='".$bar->status."'";
// 		        $optjns = makeOption($dbname, 'keu_5jenisdisposalasset','id,keterangan',$whr);
// 				$opttipelokasiasset = makeOption($dbname, 'keu_5tipelokasiasset', 'kodelokasi,namalokasi', "kodelokasi='".$bar->tipelokasi."'");
// 				$no+=1;
// 				$frm[1].=" <tr class=rowcontent>
//                 <td align=center>".$no."</td>
//                 <td width=10 align=center>".$bar->kodeorg."</td>
// 				<td>".$bar->posisiasset."</td>
// 				<td>".$opttipelokasiasset[$bar->tipelokasi]."</td>
// 				<td>".$bar->namatipe."</td>
// 				<td width=70 align=center>".$bar->kodeasset."</td>
// 				<td width=70 align=center>".$bar->kodeassetlama."</td>
// 				<td>".$bar->namasset."</td>
// 				<td width=20 align=center>".tanggalnormal($bar->tahunperolehan)."</td>
// 				<td width=20 align=center>".$optjns[$bar->status]."</td>
// 				<td width=100 align=right>".number_format($bar->hargaperolehan,2,'.',',')."</td>
// 				<td width=20 align=right>".$bar->jlhblnpenyusutan."</td>
// 				<td align=right>".$bar->persendecline."</td>
// 				<td align=center>".($bar->awalpenyusutan)."</td>
			
// 				<td align=center>".tanggalnormal($bar->tanggaldisposal)."</td>
// 				<td>".$bar->nomesin."</td>
// 				<td>".$bar->norangka."</td>
// 				<td>".$bar->keterangan."</td>
// 				<td>".$kamusleasing[$bar->leasing]."</td>
// 				<td>".$bar->kodeproject."</td>
// 				<td align=center>
// 				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editAsset('".$bar->kodeorg."','".$bar->tipeasset."','".$bar->kodeasset."','".$bar->namasset."','".$bar->kodebarang."','".tanggalnormal($bar->tahunperolehan)."','".$bar->status."','".number_format($bar->hargaperolehan)."','".$bar->jlhblnpenyusutan."','".($bar->awalpenyusutan)."','".$bar->keterangan."','".$bar->leasing."','".$bar->penambah."','".$bar->pengurang."','".$bar->refbayar."','".$bar->dokpengadaan."','".$bar->persendecline."','".$bar->posisiasset."','".$bar->induk."','".$bar->subtipe."','".$bar->jenis_biaya."','".tanggalnormal($bar->tanggaldisposal)."','".$bar->kodeassetlama."','".$bar->tipelokasi."','".$bar->nomesin."','".$bar->norangka."');\">";
				
// 				if(file_exists($folder.$bar->kodeasset.".png")){
// 					$frm[1].="&nbsp;<img src='images/skyblue/zoom.png' class='resicon' onclick=\"viewfile('event','".$bar->kodeasset.".png')\">";
// 				}
// 				$frm[1].="&nbsp <!--<img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delAsset('".$bar->kodeorg."','".$bar->kodeasset."');\">-->
//                 </td>
//             </tr>";
//         }
//   $frm[1].="<tr><td colspan=17 align=center>
//        ".((@$page*$limit)+1)." to ".((@$page+1)*$limit)." Of ".  @$jlhbrs."
//            <br>
//        <button class=mybutton onclick=cariAsset(".($page-1).");>".$_SESSION['lang']['pref']."</button>
//            <button class=mybutton onclick=cariAsset(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
//            </td>
//            </tr>";	  	   
// $frm[1].="
echo "
                 </tbody>
                 <tfoot>
                 </tfoot>
                 </table>
                 </div>
                 </fieldset>
                ";	 

// $hfrm[0]=$_SESSION['lang']['inputaset'];
// $hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
// drawTab('FRM',$hfrm,$frm,250,'100%');
CLOSE_BOX();
echo close_body();
?>

<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$kelompokvhc     =checkPostGet('kelompokvhc','');
$jenisvhc        =checkPostGet('jenisvhc','');
$kodeorg         =checkPostGet('kodeorg','');
$method          =checkPostGet('method','');
$kodevhc         =str_replace(" ","",checkPostGet('kodevhc',''));
$tahunperolehan  =checkPostGet('tahunperolehan','');
$noakun          =checkPostGet('noakun','');
$beratkosong     =checkPostGet('beratkosong','');
$nomorrangka     =checkPostGet('nomorrangka','');
$nomormesin      =checkPostGet('nomormesin','');
$detailvhc       =checkPostGet('detailvhc','');
$kodebarang      =checkPostGet('kodebarang','');
$kepemilikan     =checkPostGet('kepemilikan','');
$kodetraksi      =checkPostGet('kodetraksi','');
$nobpkb          =checkPostGet('nobpkb','');
$kodesupplier    =checkPostGet('kodesupplier','');
$kodeasset       =checkPostGet('kodeasset','NULL');
$tglakhirstnk    =tanggalsystemn(checkPostGet('tglakhirstnk','00-00-0000'));
$tglakhirkir     =tanggalsystemn(checkPostGet('tglakhirkir','00-00-0000'));
$tglakhirijinbm  =tanggalsystemn(checkPostGet('tglakhirijinbm','00-00-0000'));
$tglakhirijinang =tanggalsystemn(checkPostGet('tglakhirijinang','00-00-0000'));
$nopol           =checkPostGet('nopol','');
$tahunproduksi   =checkPostGet('tahunproduksi','');
$warna           =checkPostGet('warna','');
$statusvhc       =checkPostGet('statusvhc','');
$tglakhirleasing =tanggalsystemn(checkPostGet('tglakhirleasing','00-00-0000'));
$tglakhirasuransi=tanggalsystemn(checkPostGet('tglakhirasuransi','00-00-0000'));


if($kodeasset!='NULL') {
	$kodeasset = "'".$kodeasset."'";
}

if($kodeasset=="''"){
	$kodeasset = 'null';
}
else
{
	$kodeasset = $kodeasset;
}

if($beratkosong=='') $beratkosong=0;


$str = "select *  from " . $dbname . ".keu_5akun WHERE aktif=1";
$res = fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$bar['namaakun'];
	$tipeakun[$bar['noakun']]=$bar['tipeakun'];
}
$str = "select *  from " . $dbname . ".setup_klpkegiatan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmkel[$bar['kodeklp']]=$bar['namakelompok'];
}


$str = "select *  from " . $dbname . ".organisasi where induk=''";
$res = fetchdata($str);
foreach($res as $bar){
	$holding=$bar['kodeorganisasi'];
}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$arrpremi=array('1'=>'Premi di BKM dikunci','0'=>'Premi di BKM tidak dikunci');

$optklvhc="<option value=''></option>";
$arrklvhc=getEnum($dbname,'vhc_5master','kelompokvhc');
$nmklvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');


foreach($arrklvhc as $kei=>$fal){
	switch($kei){
		case 'AB':
			 $_SESSION['language']!='EN'?$fal='Alat Berat':$fal='Heavy Equipment';
		break;
		case 'KD':                            
			$_SESSION['language']!='EN'?$fal='Kendaraan':$fal='Vehicle';
		break;
		case 'MS':
			$_SESSION['language']!='EN'? $fal='Mesin':$fal='Machinery';
		break;
	}
	$optklvhc.="<option value='".$kei."'>".$kei." - ".$fal."</option>";
} 
//ambil jenis mesin/kendaraan
$str="select * from ".$dbname.".vhc_5jenisvhc order  by namajenisvhc";
$optjnsvhc="<option value=''></option>";;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
  	$optjnsvhc.="<option value='".$bar->jenisvhc."'>".$bar->jenisvhc." - ".$bar->namajenisvhc."</option>";
}	 

//=================ambil master barang untuk aset kendaraan (905)
$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='911' order by namabarang";
$optbarang="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar->kodebarang."'>".$bar->kodebarang." - ".$bar->namabarang."</option>";	
}

//=================ambil master supplier untuk pemilik kendaran sewa
$str="select supplierid,namasupplier from ".$dbname.".log_5supplier where status='1' order by namasupplier";
$optsupplier="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optsupplier.="<option value='".$bar->supplierid."'>".$bar->supplierid." - ".$bar->namasupplier."</option>";	
}
#ambil traksi
if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL')){
  $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' order by namaorganisasi";
}else{
  $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi";
}
  
$opttraksi='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $opttraksi.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
}  
  
//ambil kode organisasi selain blok dan afdeling
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe  in('KANWIL','HOLDING','KEBUN','PABRIK','TRAKSI','BULKING','TC','RND') 
and length(kodeorganisasi)=4 order  by kodeorganisasi,namaorganisasi";
$optorg="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
  	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}	 
    
$optkepemilikan=" <option value=1>".$_SESSION['lang']['miliksendiri']."</option>
                  <option value=0>".$_SESSION['lang']['sewa']."</option>";
$optAsset = "<option value=''></option>";

switch($method){
	case 'loadjenis':
		$str="select * from ".$dbname.".vhc_5jenisvhc where kelompokvhc='".$param['kelompok']."' order  by namajenisvhc";
        $optjnsvhc="<option value=''></option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $optjnsvhc.="<option value='".$bar->jenisvhc."'>".$bar->namajenisvhc."</option>";
        }
        echo  $optjnsvhc; 
	break;	
	case 'getnoakun':
		$str = "select *  from " . $dbname . ".setup_klpkegiatan where kodeklp='".$param['kelompok']."' and kodeorg='".$holding."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$akun2=$bar['noakun'];
		}
		
		$arrnoakun=explode(',',$akun2);
		$jumlahnoakun=count($arrnoakun);
		// exit("Error:$jumlahnoakun");
		$nourut=0;
		foreach($arrnoakun as $dtakun){
			
			
			if($nourut>'0'){
				@$whereakun.=" or noakun like '".$dtakun."%' ";
			}else{
				@$whereakun.=" and noakun like '".$dtakun."%' ";
			}
			$nourut++;
		}
		
	
		$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".keu_5akun where 1=1 ".$whereakun." and namaakun not like '%non aktif%' and aktif='1'";
		// echo $str;exit("error:");
		$res = fetchData($str);
		foreach($res as $val){
			if(strlen($val['noakun'])==3){
				$d=$val['noakun'];
				if($d!=$n){			
					$optakun.="<optgroup label='".$d." - ".$val['namaakun']."'>";
				}
			}
			if(strlen($val['noakun'])==7){
				$sel="";
				if($param['valakun']==$val['noakun']){$sel="selected";}
				$optakun.="<option value=".$val['noakun']." ".$sel.">".$val['noakun']." - ".$val['namaakun']."</option>";			
			}
			
			$n=$d;
			if($d!=$n){			
				$optakun.="</optgroup>";
			}
		}
		
		echo $optakun;
	break;
	case 'addnew':
		echo"<fieldset>
                <table border=0 ><legend>".$_SESSION['lang']['entryForm']."</legend>
                    <tr>	
						<td>".$_SESSION['lang']['kodetraksi']." <font size=3px style=color:red;><b>*</b></font></td>
						<td><select class=select2  style=width:250px; id=kodetraksi tabindex='1'>".$opttraksi."</select></td>
                        
                        <td>".$_SESSION['lang']['tahunperolehan']." <font size=3px style=color:red;><b>*</b></font></td>
                        <td><input style=width:100px type=text id=tahunperolehan size=4 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 tabindex='7'></td>
						
                        <td>".$_SESSION['lang']['warna']."</td>
                        <td><input style=width:156px type=text id=warna onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtext maxlength=20 tabindex='13'></td>
                        <td style=display:none; colspan=2 rowspan=7 style='vertical-align:top'>
                            <div id='divimage' style='padding-top:5px;padding-left:5px;border:1px solid grey;text-align:center'>
                                <img src='images/question.png' style='width:120px;height:120px;'>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['kodeorganisasi']." (Owner) <font size=3px style=color:red;><b>*</b></font></td>
                        <td><select class=select2  style=width:250px; id=kodeorg onchange=getList(); tabindex='2'>".$optorg."</select></td>
                        
                        <td >".$_SESSION['lang']['tahunproduksi']." <font size=3px style=color:red;><b>*</b></font></td>
                        <td><input style=width:100px type=text id=tahunproduksi size=4 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4 tabindex='8'></td>
						
                        <td style=width:160px>".$_SESSION['lang']['nopol']."</td>
                        <td><input style=width:156px type=text  id=nopol size=12 onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtext maxlength=20 tabindex='14'></td>
                    </tr>
                    <tr>
                        <td style=width:160px hidden>".$_SESSION['lang']['namabarang']."</td>
                        <td style=width:220px hidden><select class=select2  style=width:200px; id=kodebarang style='width:200px'>".$optbarang."</select></td>
						
						<td style=width:160px>".$_SESSION['lang']['kodekelompok']." <font size=3px style=color:red; valign=center><b>*</b></font></td>
                        <td  style=width:250px ><select class=select2 style=width:250px; id=kelompokvhc onchange=gettipekendaraan() tabindex='3'>".$optklvhc."</select></td>
                        
                        <td>".$_SESSION['lang']['tglakhirstnk']."</td><td>
                        <input type=text class=myinputtext id=tglakhirstnk name=tglakhirstnk onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; readonly/ tabindex='9'></td>
                        
                        <td style=width:160px>".$_SESSION['lang']['nomormesin']."</td>
                        <td><input style=width:156px type=text id=nomormesin size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45 tabindex='15'></td>
                    </tr>
                    <tr>
						<td width=180px>".$_SESSION['lang']['jenkendabmes']." <font size=3px style=color:red;><b>*</b></font></td>
						<td><select class=select2 style=width:250px id=jenisvhc  tabindex='4'></select></td>
						
                        <td>".$_SESSION['lang']['tglakhirkir']."</td>
                        <td><input type=text class=myinputtext id=tglakhirkir name=tglakhirkir onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; readonly/ tabindex='10'></td>

						<td style=width:160px>".$_SESSION['lang']['nomorrangka']." / ".$_SESSION['lang']['noseri']."</td>
						<td><input style=width:155px;  type=text id=nomorrangka size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=20 tabindex='16'></td>
                    </tr>
                    <tr>
						<td>".$_SESSION['lang']['kodeasset']."</td>
						<td><select class=select2 style=width:250px; id=kodeasset tabindex='5'>".$optAsset."</select></td>
                        
                        <td>".$_SESSION['lang']['tglakhirleasing']."</td>
                        <td><input type=text class=myinputtext id=tglakhirleasing name=tglakhirleasing onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; readonly/ tabindex='11'></td>
						
                        <td style=width:160px>".$_SESSION['lang']['nobpkb']."</td>
                        <td><input style=width:156px type=text id=nobpkb size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=20 tabindex='17'></td>
                    </tr>
                    <tr>
						<td>".$_SESSION['lang']['kepemilikan']."</td>
						<td><select class=select2  style=width:250px id=kepemilikan tabindex='6'>".$optkepemilikan."</select></td>
                        
                        <td>".$_SESSION['lang']['tglakhirasuransi']."</td>
                        <td><input type=text class=myinputtext id=tglakhirasuransi name=tglakhirasuransi onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; readonly/ tabindex='12'></td>

                        <td style=width:160px>".$_SESSION['lang']['beratkosong']." (Kg)</td>
                        <td><input style=width:156px type=text id=beratkosong size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5 tabindex='18'></td>
                        
                        <td hidden>".$_SESSION['lang']['tglakhirijinbongkar']."</td>
                        <td hidden><input type=text class=myinputtext id=tglakhirijinbm name=tglakhirijinbm onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; readonly/></td>

						<td hidden>".$_SESSION['lang']['tglakhirijinangkut']."</td>
						<td hidden><input type=text class=myinputtext id=tglakhirijinang name=tglakhirijinang onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; readonly/></td>   
                    </tr>
                    <tr>
						<td valign=top style=width:160px>".$_SESSION['lang']['kodevhc']."</td>
						<td valign=top><input style=width:246px type=text disabled placeholder='auto generate' id=kodevhc size=12 onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtext maxlength=20></td>

						<td valign=top width=180px rowspan=2>".$_SESSION['lang']['tmbhDetail']." Keterangan</td>
						<td colspan=3 rowspan=2><textarea  id=detailvhc cols=54 rows=2 onkeypress=\"return tanpa_kutip(event);\" maxlength=255 tabindex='19'></textarea ></td>
                    </tr>
					<tr>
						<td valign=top style=width:160px>Pemilik Alat/Kendaraan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<i>isi jika Sewa/Kontrak</i>)</td>
						<td valign=top><select class=select2  style=width:110px id=kodesupplier tabindex='6'>".$optsupplier."</select>
						&nbsp;&nbsp;".$_SESSION['lang']['status']."<input type=checkbox id=statusvhc checked>(v) => Aktif</td></td>
					</tr>
					<tr>
						<td colspan=6><hr></td>
					</tr>
                    <tr>
                        <td colspan=6 align=center>
							<input type=hidden id=method value='insert'>
							<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
							<button class=mybutton onclick=cancelMasterVhc()>".$_SESSION['lang']['cancel']."</button>
						</td>
					</tr>
					<tr><td colspan=6><i><b><font size=3px style=color:red;><b>*</b></font>) Kolom yang wajib terisi.</b></i></td></tr>
                </table>
            </fieldset>";
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
            <tr class=rowheader>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodeorganisasi']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodekelompok']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jenkendabmes']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodevhc']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nopol']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodeasset']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tahunperolehan']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nomormesin']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['detail']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kepemilikan']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodetraksi']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['status']."</th>
                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                <th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
            </tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";

        $where='1=1';
		
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		// foreach($arrunit as $val=>$nama){
		// 	$dtunit[$val]=$val;
		// }
		// $where.=" and kodeorg in ('".implode("','",$arrunit)."') ";
		$detailAkses = array();
		foreach ($arrunit as $key => $value) {
			array_push($detailAkses, "'" . $key . "'");
		}
		$where.="".( isset($detailAkses) ? " AND kodeorg in (".implode(',', $detailAkses).") " : "")." ";
		
		// if($kodeorg!='')
		// 	$where.=" and kodeorg='".$kodeorg."' ";
		if($kelompokvhc!='')
			$where.=" and kelompokvhc='".$kelompokvhc."' ";   
		if($jenisvhc!='')
			$where.=" and jenisvhc='".$jenisvhc."' ";
			
		// if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL')){
			// $str="select * from ".$dbname.".vhc_5master where ".$where." 
				// order by status desc,kodeorg,kodevhc asc";
		// } else{
			// $str="select * from ".$dbname.".vhc_5master where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%' and ".$where." 
				// order by status desc,kodeorg,kodevhc asc";
		// }
		
		$str="select * from ".$dbname.".vhc_5master where ".$where."  order by status desc,kodeorg,kodevhc asc";
		$no=0;
		$listAsset = array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res->fetch())
		{
			$no+=1;
			$str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar1->kodebarang."'";
			$namabarang='';
				$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res1->fetch())
				{
					$namabarang=$bar->namabarang;
			}
			
			if($bar1->kepemilikan==1) {
				$dptk=$_SESSION['lang']['miliksendiri'];	
			} else {
				$dptk=$_SESSION['lang']['sewa'];
			}
			$sttd="";
			$sttd="Non aktifkan";
			$bgcrcolor="class=rowcontent";
			if($bar1->status==0){
				$bgcrcolor="bgcolor=orange";
				$sttd="";
				$sttd="Aktifkan";
			}
			// $clidt=" style='cursor:pointer' title='".$sttd." ".$bar1->kodevhc."' onclick=deAktif('".$bar1->kodevhc."','".$bar1->status."');" ;
			#<td ".$clidt." >".$namabarang."</td>
			$tab.="<tr ".$bgcrcolor.">
				<td align=center ".$clidt." >".$no."</td>
				<td align=center  ".$clidt." >".$bar1->kodeorg."</td>
				<td align=center  ".$clidt." >".$bar1->kelompokvhc."</td>				 
				<td align=center  ".$clidt." nowrap>".$bar1->jenisvhc." - ".$nmklvhc[$bar1->jenisvhc]."</td>			 		
				<td ".$clidt." >".$bar1->kodevhc."</td>
				<td  ".$clidt."  nowrap>".$bar1->nopol."</td>
				<td  ".$clidt."  >".$bar1->kodeasset."</td>
				<td align=center  ".$clidt." >".$bar1->tahunperolehan."</td>
				<input type=hidden value=".$bar1->beratkosong.">
				<input type=hidden value=".$bar1->nomorrangka.">
				<td ".$clidt." >".$bar1->nomormesin."</td> 
				<td ".$clidt." >".$bar1->detailvhc."</td> 	
				<td ".$clidt." >".$dptk."</td> 
				<td align=center  ".$clidt." nowrap>".getNamaOrg($bar1->kodetraksi)."</td>
				<td align=center  ".$clidt." >".$arrstatus[$bar1->status]."</td>
				<td align=center  ".$clidt." >".($bar1->updateby == '0000000000' ? getNamaKaryawan($bar1->createdby) : getNamaKaryawan($bar1->updateby))."</td>
				<td align=center>
                    <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editdata('edit','".$bar1->kodeorg."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$bar1->kodevhc."','".$bar1->beratkosong."','".$bar1->nomorrangka."','".$bar1->nobpkb."','".$bar1->nomormesin."','".$bar1->tahunperolehan."','".$bar1->kodebarang."','".$bar1->kepemilikan."','".$bar1->kodetraksi."','".tanggalnormal($bar1->tglakhirstnk)."','".tanggalnormal($bar1->tglakhirkir)."','".tanggalnormal($bar1->tglakhirijinbm)."','".tanggalnormal($bar1->tglakhirijinang)."','".$bar1->kodeasset."','".ilanginenter($bar1->detailvhc)."','".$bar1->nopol."','".$bar1->tahunproduksi."','".$bar1->warna."','".tanggalnormal($bar1->tglakhirleasing)."','".tanggalnormal($bar1->tglakhirasuransi)."','".$bar1->status."');\">
					<!--<img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"deleteMasterVhc('".$bar1->kodeorg."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$bar1->kodevhc."');\">-->
                </td>
                </tr>";
			
			if($bar1->kodeasset!=str_replace("'",'',$kodeasset)) {
				$listAsset[] = $bar1->kodeasset;
			}
		}
        
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;	
	break;
    case 'delete':
        $strx="delete from ".$dbname.".vhc_5master where kodevhc='".$kodevhc."'";
			try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
    case 'getNotransaksi':
		//bentuk nomor transaksi
        $str="select max(right(kodevhc,4)) as nomorurut from ".$dbname.".vhc_5master where kodeorg = '".$kodeorg."' and jenisvhc='".$jenisvhc."' order by right(kodevhc,4) desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $notran=$kodeorg.$jenisvhc.addZero($noawal,4);
		echo $notran;
		// exit('error'.$notran);
	
	break;
    case 'update':
		$strx="update ".$dbname.".vhc_5master set jenisvhc='".$jenisvhc."',
			kelompokvhc='".$kelompokvhc."', 
			kodeorg='".$kodeorg."', tahunperolehan='".$tahunperolehan."',
			beratkosong='".$beratkosong."', nomorrangka='".$nomorrangka."' , nobpkb='".$nobpkb."' ,
			nomormesin='".$nomormesin."',detailvhc='".$detailvhc."',
			kodebarang='".$kodebarang."',kepemilikan=".$kepemilikan.",
			kodetraksi='".$kodetraksi."', tglakhirstnk='".$tglakhirstnk."',
			tglakhirkir='".$tglakhirkir."',tglakhirijinbm='".$tglakhirijinbm."',
			tglakhirijinang='".$tglakhirijinang."',kodeasset=".$kodeasset.",
			nopol='".$nopol."',tahunproduksi='".$tahunproduksi."',warna='".$warna."',status='".$statusvhc."',kodesupplier='".$kodesupplier."',
			tglakhirleasing='".$tglakhirleasing."',tglakhirasuransi='".$tglakhirasuransi."',updateby='" . $_SESSION['standard']['userid'] . "' 
			where kodevhc='".$kodevhc."'";
			try{$owlPDO->exec($strx); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
			echo trim($kodevhc);
    break;	
    case 'insert':
		//bentuk nomor transaksi
        $str="select count(kodevhc) as nomorurut from ".$dbname.".vhc_5master where kodeorg = '".$kodeorg."' and jenisvhc='".$jenisvhc."' and tahunperolehan='".$tahunperolehan."' order by right(kodevhc,4) desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $kodevhc=$kodeorg.$jenisvhc.substr($tahunperolehan,2,2).addZero($noawal,2);
		
        //cek apakah sudah ada data
        $sql="select kodevhc from ".$dbname.".vhc_5master where kodevhc='".$kodevhc."'";
        $hsl=fetchdata($sql);
        if(count($hsl) < 1){
            $strx="insert into ".$dbname.".vhc_5master(
                kodevhc,kelompokvhc,kodeorg,jenisvhc,
                tahunperolehan,beratkosong,nomorrangka,nobpkb,
                nomormesin,detailvhc,kodebarang,kepemilikan,kodetraksi,
                tglakhirstnk,tglakhirkir,tglakhirijinbm,tglakhirijinang,kodeasset,
                nopol,tahunproduksi,warna,tglakhirleasing,tglakhirasuransi,createdby,createdtime,status,kodesupplier)
            	values('".$kodevhc."','".$kelompokvhc."',
                '".$kodeorg."','".$jenisvhc."',".$tahunperolehan.",
                ".$beratkosong.",'".$nomorrangka."','".$nobpkb."','".$nomormesin."',
                '".$detailvhc."','".$kodebarang."',".$kepemilikan.",
                '".$kodetraksi."','".$tglakhirstnk."','".$tglakhirkir."',
                '".$tglakhirijinbm."','".$tglakhirijinang."',".$kodeasset.",
                '".$nopol."','".$tahunproduksi."','".$warna."','".tanggalsystemn($param['tglakhirleasing'])."','".tanggalsystemn($param['tglakhirasuransi'])."',
                '" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."','".$statusvhc."','".$kodesupplier."')";
                
                try{$owlPDO->exec($strx); }
                catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                    die(); 
                }
				echo trim($kodevhc);
        }else{
            exit("Warningsistem : Data dengan Kode Kendaraan ".$kodevhc." sudah ada !");
        }
	break;	
    case'deactive':
        if($_POST['status']==1){
            $_POST['status']=0;
        }else{
            $_POST['status']=1;
        }
          $strx="update ".$dbname.".vhc_5master set status='".$_POST['status']."',updateby='".$_SESSION['standard']['userid']."' 
                 where kodevhc='".$_POST['kodevhc']."'";
		try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
	case'getList':
		// Get Kode Asset
		if(!empty($kodeorg)) {
			$whereAsset = "kodeorg='".$kodeorg."' and kodeasset not in 
			(SELECT kodeasset FROM ".$dbname.".vhc_5master where kodeasset !='') and tipeasset='".$kelompokvhc."'";
									   
            $optAsset="<option value=''></option>";
            $str="select * from ".$dbname.".sdm_daftarasset where 1=1 and ".$whereAsset." ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$optAsset.="<option value=".$bar['kodeasset'].">".$bar['kodeasset']." - ".$bar['namasset']."</option>";
			}

			if ($kodeasset!='NULL') {
				$kodeasset=str_replace("'","",$kodeasset);
				$whrKar2="kodeasset='".$kodeasset."'";
                $optjenis=makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',$whrKar2);
				$optAsset.="<option value=".$kodeasset." selected>".$kodeasset." - ".$optjenis[$kodeasset]."</option>";
			}
			echo $optAsset; 
		}
	break;
	case 'gettipekendaraan':
		$sql="select jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc where kelompokvhc='".$kelompokvhc."'";
		$qry=fetchData($sql);
		$opttipevhc="<option value=''></option>";
		foreach ($qry as $val) {
			$opttipevhc.="<option value='".$val['jenisvhc']."'>".$val['jenisvhc']." - ".$val['namajenisvhc']."</option>";
		}
		echo $opttipevhc;
	break;
}

function ilanginenter($tulisan){
	$buffer = str_replace(array("\r", "\n"), '', $tulisan);
	return $buffer;
}
?>
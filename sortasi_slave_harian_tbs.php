<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/zFunction.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$method = checkPostGet('method', '');
$caritanggal = checkPostGet('caritanggal', '');
$pages = checkPostGet('page', '');

$unit = checkPostGet('unit', '');

$station = checkPostGet('station', '');

$substation = checkPostGet('substation', '');
$hour = checkPostGet('hour', '0');
$hourproses = checkPostGet('hourproses', '0');
$keterangan = checkPostGet('keterangan', '');
$current = checkPostGet('current', '');

$app = 'pabrik';
$postJabatan = getPostingJabatan($app);

$tipe = checkPostGet('tipe', '');
$pt=checkPostGet('pt', '');
$kebun=checkPostGet('kebun', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));
$iddivisi=checkPostGet('iddivisi','');

$id = checkPostGet('id', '');
$idht=checkPostGet('idht', '');
$tipe = checkPostGet('tipe','');
$jamsample = checkPostGet('jamsample', '');
$menitsample = checkPostGet('menitsample', '');
$tatam=checkPostGet('tatam', '');
$totjang = checkPostGet('totjang', '');
$tonase=checkPostGet('tonase', '');
$fraksi1 = checkPostGet('fraksi1','');
$fraksi2 = checkPostGet('fraksi2', '');
$fraksi3=checkPostGet('fraksi3', '');
$fraksi4 = checkPostGet('fraksi4', '');
$abnormal=checkPostGet('abnormal', '');
$emptybunch = checkPostGet('emptybunch','');
$dura = checkPostGet('dura', '');
$comidel=checkPostGet('comidel', '');
$tangjang = checkPostGet('tangjang', '');
$tot=checkPostGet('tot', '');
$brondol = checkPostGet('brondol','');
$oer = checkPostGet('oer', '');
$curah=checkPostGet('curah', '');

$valtipe=($tipe=='1'?'Internal':'External');



switch($proses)
{
	case'insertht':
		$_SESSION['sorimage'] = array();
		if($tipe=='1'){
			$tip='Divisi';
			$str="select * from ".$dbname.".organisasi where induk='".$kebun."' and tipe='AFDELING'";
			$res=fetchData($str);
			$arrdiv = $res;
			
			$str="select * FROM ".$dbname.".sortasi_harian_kebunht where tanggal='".$tanggal."' and tipe='".$valtipe."' and kebun='".$kebun."'";
			$res=fetchdata($str);
			$countdata = count($res);
			$idht = $res[0]['id'];
		}else{
			$arrdiv = array();
			$tip='Supplier';
			$str="select * from ".$dbname.".pabrik_timbangan where kodeorg='' and kodebarang='40000003' and tanggal like '".$tanggal."%' and millcode='".$unit."' order by kodecustomer";
			$res=fetchData($str);
			$no=0;
			foreach($res as $key=>$val){
				$expwaktu = explode(":",$val['jammasuk']);
				$kodeorganisasi = getSupNameWb($val['kodecustomer'])." ".$val['nokendaraan'];
				$kdorg = getSupNameWb($val['kodecustomer'])."/".$val['nokendaraan']."/".$expwaktu[0]."/".$expwaktu[1];
				$arrdiv[$no]['kodeorganisasi'] = $kdorg;
				$arrdiv[$no]['namaorganisasi'] = $kodeorganisasi;
				$arrdt[$kdorg]['jam'] = $expwaktu[0];
				$arrdt[$kdorg]['menit'] = $expwaktu[1];
				$arrdt[$kdorg]['tahuntanam'] = $val['thntm1'];
				$arrdt[$kdorg]['totjang'] = $val['jumlahtandan1'];
				$arrdt[$kdorg]['tonase'] = $val['beratmasuk']-$val['beratkeluar'];
				$no++;
			}
			
			$str="select * FROM ".$dbname.".sortasi_harian_kebunht where tanggal='".$tanggal."' and tipe='".$valtipe."'";
			$res=fetchdata($str);
			$countdata = count($res);
			$idht = $res[0]['id'];
		}
		
		if($method=='insert'){
			if($countdata>=1){
				exit("Gagal, Data untuk tanggal ".tanggalnormal($tanggal)." sudah diinput");
			}
		}else{
			$str="select * from ".$dbname.".sortasi_harian_kebundt where idht='".$idht."'";
			$res=fetchData($str);
			$arrdt = array();
			foreach($res as $key=>$val){
				$expwaktu = explode(":",$val['waktupengsampel']);
				$arrdt[$val['tipe']]['jam'] = $expwaktu[0];
				$arrdt[$val['tipe']]['menit'] = $expwaktu[1];
				
				$arrdt[$val['tipe']]['tahuntanam'] = $val['tahuntanam']; 
				$arrdt[$val['tipe']]['totjang'] = $val['totjang']; 
				$arrdt[$val['tipe']]['tonase'] = $val['tonase']; 
				$arrdt[$val['tipe']]['mentahjjng'] = $val['mentahjjng']; 
				$arrdt[$val['tipe']]['satujjng'] = $val['satujjng']; 
				$arrdt[$val['tipe']]['duajjng'] = $val['duajjng']; 
				$arrdt[$val['tipe']]['tigajjng'] = $val['tigajjng']; 
				$arrdt[$val['tipe']]['ababnormaljjng'] = $val['ababnormaljjng']; 
				$arrdt[$val['tipe']]['emptybunchjjng'] = $val['emptybunchjjng']; 
				$arrdt[$val['tipe']]['durajjng'] = $val['durajjng']; 
				$arrdt[$val['tipe']]['tangkaipanjang'] = $val['tangkaipanjang']; 
				$arrdt[$val['tipe']]['comidel'] = $val['comidel']; 
				$arrdt[$val['tipe']]['totalkgtbs'] = $val['totalkgtbs']; 
				$arrdt[$val['tipe']]['brondolkg'] = $val['brondolkg']; 
				$arrdt[$val['tipe']]['oerproduksisounding'] = $val['oerproduksisounding']; 
				$arrdt[$val['tipe']]['curahhujan'] = $val['curahhujan']; 
				$arrdt[$val['tipe']]['iddt'] = $val['id']; 
			}
		}
		
		$tab.="<fieldset style=float:left;><legend>Detail</legend>
			<table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%;text-align'>
			<thead>
				<tr class=rowheader>
              		<th rowspan=3>NO </th>
                 	<th rowspan=3 style='min-width:100px'>".$tip."</th>
                 	<th rowspan=3>WAKTU <br>PENGSAMPEL</th>
                 	<th rowspan=3>TAHUN <br>TANAM</th>
                 	<th rowspan=3>TOTAL <br>JANJANG</th>
                    <th rowspan=3>TONASE</th>
                    <th colspan=4>FRAKSI </th>
                    <th rowspan=2>AB NORMAL</th>
                    <th rowspan=2>EMPTY <br>BUNCH</th>
                    <th rowspan=2>DURA</th>
                    <th rowspan=2>TANKAI <br>PANJANG</th>
                    <th rowspan=2>COMIDEL</th>
                    <th rowspan=3>TOTAL <br>KG TBS / DIV</th>
                    <th rowspan=2>BRONDOL</th>
                    <th rowspan=3>OER %<br>PRODUKSI<br>SOUDING</th>
                    <th rowspan=3>CURAH<br>HJN(PPM)</th>
                    <th rowspan=3>FILE<br>UPLOAD</th>
				<tr class=rowcontent>
					<td align=center>MENTAH (0 %)</td>
					<td align=center>1 < 5 %</td>
					<td align=center>2 (20%)</td>
					<td align=center>3 (75)%</td>
				</tr>
				
				<tr class=rowcontent>
					<td align=center>JJNG</td>
					<td align=center>JJNG</td>
					<td align=center>JJNG</td>
					<td align=center>JJNG</td>
					<td align=center>JJNG</td>
					<td align=center>JJNG</td>
					<td align=center>JJNG</td>
					<td align=center>%</td>
					<td align=center>%</td>
					<td align=center>Kg</td> 
				</tr>
				</thead>
				<tbody>";
				$no=0;
				foreach($arrdiv as $key=>$val){
					if($val['kodeorganisasi']=='TPRE10'){
						$arrdiv[$key]['namaorganisasi'] = 'Mitra Dasal';
					}
				}
				if($tipe=='1'){
					asort($arrdiv);
				}
				
				foreach($arrdiv as $key=>$val){					
					$jm=$mnt="";
					for($i=0;$i<24;$i++)
					{
						$x=addZero($i,2);
						if($arrdt[$val['kodeorganisasi']]['jam']==$i){
							$jm.="<option value='".$x."' selected>".$x."</option>";
						}else{
							$jm.="<option value=".$x.">".$x."</option>";
						}
					}
					for($i=0;$i<60;)
					{
						$x=addZero($i,2);
						if($arrdt[$val['kodeorganisasi']]['menit']==$i){
						   $mnt.="<option value=".$x." selected>".$x."</option>";						
						}else{
						   $mnt.="<option value=".$x.">".$x."</option>";						
						}
					   $i++;
					}
					$no++;
					
					
					##ADD FILEUPLOAD
					$strx="select * from ".$dbname.".listfilesortasi where notransaksi='".$arrdt[$val['kodeorganisasi']]['iddt']."'";
					$resx=fetchData($strx);
					if(count($resx) > 0){
						foreach($resx as $keyx=>$valx){
							$newdata = array(
								'nofile'=>$no,
								'namafile'=>$valx['namafile']
							);
							
							array_push($_SESSION['sorimage'],$newdata);
							
							$copyfrom = "fileupload/sorimage/".$valx['namafile'];
							$copyto = "fileupload/tempefil/".$valx['namafile'];
							copy($copyfrom, $copyto);
						}
					}					
					
					$tab.="<tr class=rowcontent id='tr_".$no."'>
						<td style='text-align:right'>".$no."</td>
						<td style='text-align:left' id='divisi ".$no."'>".$val['namaorganisasi']."<input type=hidden id=iddivisi".$no."  value='".$val['kodeorganisasi']."'></td>
						<td style='text-align:center'>
							<select id=jamsample".$no.">".($method='insert'?$jm:$jm[$val['kodeorganisasi']]['jam'])."</select>:<select id=menitsample".$no.">".$mnt."</select>
						</td>
						<td style='text-align:center'>
							<input type='text'  size='3' id=tatam".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=4 value='".$arrdt[$val['kodeorganisasi']]['tahuntanam']."'>
						</td>
						<td style='text-align:center'>
							<input type='text'  size='7' id=totjang".$no." class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" maxlength='10' value='".$arrdt[$val['kodeorganisasi']]['totjang']."' onblur=\"gettotalfraksi('".$no."')\">
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=tonase".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['tonase']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=fraksi1".$no." class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['mentahjjng']."' onblur=\"gettotalfraksi('".$no."')\">
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=fraksi2".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['satujjng']."' onblur=\"gettotalfraksi('".$no."')\">
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=fraksi3".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['duajjng']."' onblur=\"gettotalfraksi('".$no."')\">
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=fraksi4".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['tigajjng']."' disabled>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=abnormal".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['ababnormaljjng']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=emptybunch".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['emptybunchjjng']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=dura".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['durajjng']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=tangjang".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['tangkaipanjang']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=comidel".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['comidel']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=tot".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['totalkgtbs']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=brondol".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['brondolkg']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=oer".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['oerproduksisounding']."'>
						</td>
						<td style='text-align:center'>
							<input type='text' size='7' id=curah".$no." class='myinputtextnumber' onkeypress='return angka_doang(event)' maxlength=10 value='".$arrdt[$val['kodeorganisasi']]['curahhujan']."'>
						</td>
						<td style='text-align:center'>
							<table class=sortable cellspacing=1 border=0>
								<tbody id='containerupload".$no."'></tbody>";
								foreach($_SESSION['sorimage'] as $key=>$row)
								{
									if($row['nofile']==$no){
										$tab.="<tr class='rowcontent'>";
										$tab.="<td><a href='fileupload/tempefil/".$row['namafile']."' download>".substr($row['namafile'],0,30)."...</a></td>";
										$tab.="<td style='text-align:center'>
											<img title='Delete' class=resicon onclick=\"deletefile('".$row['namafile']."','".$row['nofile']."')\" src='images/delete_32.png'>
										</td>";
										$tab.="</tr>";
									}
								}
							$tab.="<tbody>
								<tr>
									<td>
										<input type='file' name='upload".$no."' id='upload".$no."' class=mybutton>
									</td>
									<td style='text-align:center'>
										<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"addfile('".$no."');\">
									</td>
								</tr>
								</tbody>
							</table>
						</td>
					</tr>";
				}
				$tab.="<center><tr>
					<td colspan=19 style='text-align:center'> 
						<button class=mybutton id='simpanht' onclick=savedt('".$no."')>".$_SESSION['lang']['save']."</button>&nbsp;
						<button class=mybutton id='cancelht' onclick=canceldt()>".$_SESSION['lang']['cancel']."</button> 
					</td>
				</tr></center>				
				</tbody>
				</table>
			</fieldset>";
			echo $tab;
		// }else{
			// exit('error'.'Data Sudah Ada');
		// }



		
	break;
	
	case'savedt':
		if($current=='1'){
			$str="delete from ".$dbname.".sortasi_harian_kebunht where kodeorg='".$pt."' and tipe='".$valtipe."' and kebun='".$kebun."' and tanggal='".$tanggal."'";
			// exit("error : ".$str);
			try{
				$owlPDO->exec($str); 
				
				$str = "insert into " . $dbname . ".sortasi_harian_kebunht (kodeorg,tipe,kebun,tanggal,updateby,createdby,createdtime) values('".$pt."','".$valtipe."','".($tipe=='1'?$kebun:'')."','".$tanggal."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
       
				try{
					$owlPDO->exec($str); 
				}
				catch (PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}	
		}
		
		$str="select id FROM ".$dbname.".sortasi_harian_kebunht ORDER BY id DESC LIMIT 1";
		$res=fetchdata($str);
		$idht=$res[0]['id'];

		@$perfraksi1=($fraksi1/$totjang)*100;
		@$perfraksi2=($fraksi2/$totjang)*100;
		@$perfraksi3=($fraksi3/$totjang)*100;
		@$perfraksi4=($fraksi4/$totjang)*100;
		@$perabnormal=($abnormal/$totjang)*100;
		@$peremptybunch=($emptybunch/$totjang)*100;
		@$perdura=($dura/$totjang)*100;
		@$perbrondol=($brondol/$tot)*100;
		
		$waktu = $jamsample.":".$menitsample.":00";


		$str1="insert into ".$dbname.".sortasi_harian_kebundt (idht,tipe,waktupengsampel,tahuntanam,totjang,tonase,mentahjjng,permentah,satujjng,persatu,duajjng,perdua,tigajjng,pertiga,ababnormaljjng,perababnormal,emptybunchjjng,peremptybunch,durajjng,perdura,tangkaipanjang,comidel,totalkgtbs,brondolkg,perbrondol,oerproduksisounding,curahhujan) 
		values ('".$idht."','".$iddivisi."','".$waktu."','".$tatam."','".$totjang."','".$tonase."','".$fraksi1."','".$perfraksi1."','".$fraksi2."','".$perfraksi2."','".$fraksi3."','".$perfraksi3."','".$fraksi4."','".$perfraksi4."','".$abnormal."','".$perabnormal."','".$emptybunch."','".$peremptybunch."','".$dura."','".$perdura."','".$tangjang."','".$comidel."','".$tot."','".$brondol."','".$perbrondol."','".$oer."','".$curah."')";
		try{
			$owlPDO->exec($str1);
			$myid = $owlPDO->lastInsertId();
			
			foreach($_SESSION['sorimage'] as $key=>$row)
			{
				if($current==$row['nofile']){
					$str2="insert into ".$dbname.".listfilesortasi (idht,notransaksi,namafile,status,createdby,createdtime) values ('".$idht."','".$myid."','".$row['namafile']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					try{$owlPDO->exec($str2);}catch (PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n"; die();}
					$copyfrom = "fileupload/tempefil/".$row['namafile'];
					$copyto = "fileupload/sorimage/".$row['namafile'];
					copy($copyfrom, $copyto);
					unlink($copyfrom);
				}
			}
		}catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n"; die();}
	break;
	
	case'loadData':
		$_SESSION['sorimage'] = array();
		$where = "";
		if($caritanggal!=''){
			$where = "and tanggal='".tanggalsystem($caritanggal)."'";
		}
		
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$str = "select count(*) jmlhrow from ".$dbname.".sortasi_harian_kebunht where 1=1 ".$where."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jlhbrs= $bar['jmlhrow'];	
		}
		
		$str1 = "select * from ".$dbname.".sortasi_harian_kebunht where 1=1 ".$where."  order by tanggal desc limit ".$offset.",".$limit." ";
		$no=0;
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res1->fetch()){
			$optpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$optkebun = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kebun']."'");
			$optupd = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$optpos = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['postedby']."'");
			$no+=1;
            $tab.="<tr class=rowcontent >
				<td align=left>".$no."</td>
                <td align=left>".$optpt[$bar['kodeorg']]."</td>
                <td align=left>".$bar['tipe']."</td>
                <td align=left>".$optkebun[$bar['kebun']]."</td>
                <td align=left>".tanggalnormal($bar['tanggal'])." </td>
                <td align=left>".$optupd[$bar['updateby']]." </td>
                <td align=left>".$optpos[$bar['postedby']]." </td>";
				if($bar['posted']=='1'){
					$tab.="<td colspan=2></td>
					<td>
						<img src=images/icons/04/16/02.png class=zImgOffBtn title='Posted'>
					</td>";
				}else{
					if($_SESSION['standard']['userid']==$bar['createdby']){
						$tab.="<td align=center>
							<img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('".$bar['kodeorg']."','".tanggalnormal($bar['tanggal'])."','".($bar['tipe']=='Internal'?'1':'0')."','".$bar['kebun']."');\">
						</td>
						<td align=center>
							<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"deleteall('".$bar['id']."');\">
						</td>
						<td align=center>
							<img src=images/icons/04/16/09.png class=zImgBtn title='Posting' onclick=\"postingsortasi('".$bar['id']."');\">
						</td>";
					}else{
						$tab.="<td align=center></td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center>
							<img src=images/icons/04/16/09.png class=zImgBtn title='Posting'>
						</td>";
					}
				}
				$tab.="<td style='text-align:center'>
					<img src=images/zoom.png class=zImgBtn title='print' onclick=\"showdetail('".$bar['id']."','".($bar['tipe']=='Internal'?'1':'2')."','html',event);\">
				</td>
			</tr>";  
		}
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
		
		$tab.="</tr>
            <tr><td colspan=20 align=center>";
		
		if($page=='0')
		{
			$tab.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
		}
		
		$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
		
		if(($page+1) == $totrows)
		{
			$tab.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
		}
        $tab.="</td></tr>";
		echo $tab;
	break;
	
	case'deleteall':
		$str="delete from ".$dbname.".sortasi_harian_kebunht where id='".$id."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	
	case'postingsortasi':
		$str="update ".$dbname.".sortasi_harian_kebunht set posted='1', postedby='".$_SESSION['standard']['userid']."' where id='".$id."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	
	case'showdetail':
		if($method=='html'){
			$tab.="<script type='text/javascript' language='javascript' src='js/sortasi_harian_tbs.js'></script>";
			$tab.="<script type='text/javascript' language='javascript' src='js/generic.js'></script>";
			$tab.="<link rel=stylesheet type='text/css' href='style/".$gen."'>";
			$border='0';
		}else{
			$tab.="";
			$border='1';
			$str="select * from ".$dbname.".sortasi_harian_kebunht where id='".$id."'";
			$res=fetchdata($str);
			$tglexcel = tanggalnormal($res[0]['tanggal']);
			$tipeexcel = $res[0]['tipe'];
			$hariexcel = hari($res[0]['tanggal'],'ID');
		}
		if ($tipe==1){
			$tip='Divisi';
			$str="select * from ".$dbname.".organisasi where induk='".$unit."' and tipe='AFDELING'";
			$res=fetchData($str);
			$countstation = count($res);
		}
		else
		{
			$tip='Supplier';
		}
		
		if($method=='html'){
			$tab.="<fieldset><legend>List Data</legend>";
			$tab.="<img src='images/excel.jpg' width=15px title='Excel' style='cursor:pointer' onclick=\"showdetail('".$id."','".$tipe."','excel',event);\">";
		}else{
			$tab.="<table>
				<tr>
					<td colspan=26 style='text-align:center;font-weight:bold;font-size:30px'>SORTASI TBS ".($tipe=='1'?'INTERNAL':'EKSTERNAL')."</td>
				</tr>
				<tr>
					<td colspan=26 style='text-align:left;'>DAY : ".$hariexcel."</td>
				</tr>
				<tr>
					<td colspan=26 style='text-align:left;'>DATE : ".$tglexcel."</td>
				</tr>
			</table>";
		}
        $tab.="<div style=overflow:auto;width:100%;>
		<table cellpading=1 cellspacing=1 border=".$border." class=sortable style=width:100%>
			<thead style='text-align:center'>
			<tr class=rowheader>
				<td rowspan=3 style='min-width:100px'>".$tip."</td>
                <td rowspan=3>WAKTU <br>PENGSAMPEL</td>
				<td rowspan=3>TAHUN <br>TANAM</td>
				<td rowspan=3>TOTAL <br>JANJANG</td>
				<td rowspan=3>TONASE</td>
				<td colspan=8>FRAKSI </td>
				<td colspan=2 rowspan=2>AB NORMAL</td>
				<td colspan=2 rowspan=2>EMPTY <br>BUNCH</td>
				<td colspan=2 rowspan=2>DURA</td>
				<td rowspan=2>TANGKAI <br>PANJANG</td>
				<td rowspan=2>COMIDEL</td>
				<td rowspan=3>TOTAL <br>KG TBS / DIV</td>
				<td colspan=2 rowspan=2>BRONDOL</td>
				<td rowspan=3>OER %<br>PRODUKSI<br>SOUDING</td>
				<td rowspan=3>CURAH<br>HJN(PPM)</td>";
				if($method=='html'){
					$tab.="<td colspan=2 rowspan=3>File<br>Upload</td>";
				}
			$tab.="</tr>
			<tr class=rowcontent>
				<td align=center colspan=2>MENTAH (0 %)</td>
				<td align=center colspan=2>1 < 5 %</td>
				<td align=center colspan=2>2 (20%)</td>
				<td align=center colspan=2>3 (75)%</td>
			</tr>
			
			<tr class=rowcontent>
				<td align=center>JJNG</td>
                <td align=center>%</td>
                <td align=center>JJNG</td>
                <td align=center>%</td>
                <td align=center>JJNG</td>
                <td align=center>%</td>
                <td align=center>JJNG</td>
                <td align=center>%</td>
				<td align=center>JJNG</td>
				<td align=center>%</td>
				<td align=center>JJNG</td>
				<td align=center>%</td>
				<td align=center>JJNG</td>
				<td align=center>%</td>
				<td align=center>%</td>
				<td align=center>%</td>
				<td align=center>Kg</td>
				<td align=center>%</td>
			</tr>
            </thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".sortasi_harian_kebundt where idht=".$id." order by tipe asc";
		$res=fetchdata($str);
        $totalitemtangkaipanjang = $totalitemcomidel = 0;
		$grdtotaljjg = $grdtonase = 0;
		foreach($res as $key=>$bar){
			if($bar['tipe']=='TPRE10'){
				$res[$key]['tipe'] = "Mitra Dasal";
			}
		}
		if($tipe=='1'){
			array_sort_by_column($res, 'tipe');
		}

		foreach($res as $key=>$bar){
			if($tipe=='1'){
				$optDiv = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['tipe']."'");
			}else{
				$exptipe = explode('/',$bar['tipe']);
				$optDiv[$bar['tipe']] = ($exptipe[0])." ".$exptipe[1];
			}
			$tab.="<tr class=rowcontent>
				<td>".($bar['tipe']=='Mitra Dasal'?'Mitra Dasal':$optDiv[$bar['tipe']])."</td>
				<td style='text-align:center'>".$bar['waktupengsampel']."</td>
				<td style='text-align:center'>".$bar['tahuntanam']."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['totjang'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['tonase'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['mentahjjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['permentah'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['satujjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['persatu'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['duajjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['perdua'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['tigajjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['pertiga'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['ababnormaljjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['perababnormal'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['emptybunchjjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['peremptybunch'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['durajjng'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['perdura'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['tangkaipanjang'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['comidel'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['totalkgtbs'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['brondolkg'],0)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['perbrondol'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['oerproduksisounding'],2)."</td>
				<td style='text-align:right'>".hidezerodecimal($bar['curahhujan'],2)."</td>";
				if($method=='html'){
					$strx="select * from ".$dbname.".listfilesortasi where notransaksi='".$bar['id']."'";
					$resx=fetchdata($strx);
					if(count($resx)>0){
						$myno=0;
						foreach($resx as $keyx=>$valx){
							$myno++;
							$tab.="<td style='vertical-align:top'>".$myno.".</td>";
							$tab.="<td><a href='fileupload/sorimage/".$valx['namafile']."' download>".substr($valx['namafile'],0,30)."...</a></td>";
						}
					}else{
						$tab.="<td colspan=2></td>";
					}
				}
			$tab.="</tr>";
			
			if($bar['tangkaipanjang']>0){
				$totalitemtangkaipanjang++;
			}
			if($bar['comidel']>0){
				$totalitemcomidel++;
			}
			
			$grdtotaljjg = $grdtotaljjg + $bar['totjang'];
			$grdtonase = $grdtonase + $bar['tonase'];
			$grdtotalkgtbs = $grdtotalkgtbs + $bar['totalkgtbs'];
			$grdbrondolkg = $grdbrondolkg + $bar['brondolkg'];
			
			$rtmentahjjg = $rtmentahjjg + $bar['mentahjjng'];
			$rtsatujjng = $rtsatujjng + $bar['satujjng']; 
			$rtduajjng = $rtduajjng + $bar['duajjng']; 
			$rttigajjng = $rttigajjng + $bar['tigajjng']; 
			
			$rtababnormaljjng = $rtababnormaljjng + $bar['ababnormaljjng']; 
			$rtemptybunchjjng = $rtemptybunchjjng + $bar['emptybunchjjng']; 
			$rtdurajjng = $rtdurajjng + $bar['durajjng']; 
			
			$ttltangkaipanjang = $ttltangkaipanjang + $bar['tangkaipanjang'];
			$ttlcomidel = $ttlcomidel + $bar['comidel'];
        }
		@$rtpermentah = ($rtmentahjjg/$grdtotaljjg*100);
		@$rtpersatu = ($rtsatujjng/$grdtotaljjg*100);
		@$rtperdua = ($rtduajjng/$grdtotaljjg*100);
		@$rtpertiga = ($rttigajjng/$grdtotaljjg*100);
		
		@$rtperababnormal = ($rtababnormaljjng/$grdtotaljjg*100);
		@$rtperemptybunch = ($rtemptybunchjjng/$grdtotaljjg*100);
		@$rtperdura = ($rtdurajjng/$grdtotaljjg*100);
		
		@$rttangkaipanjang = $ttltangkaipanjang/$totalitemtangkaipanjang;
		@$rtcomidel = $ttlcomidel/$totalitemcomidel;
		
		@$rtperbrondol = ($grdbrondolkg/$grdtotalkgtbs*100);
		
		$grdfraksi = $rtpersatu+$rtperdua+$rtpertiga;
		
		$tab.="<tr class=rowcontent style='font-weight:bold'>
			<td colspan=5>RATA RATA</td>
            <td align=right>".hidezerodecimal($rtmentahjjg,0)."</td>
            <td align=right>".hidezerodecimal($rtpermentah,2)."</td>
			<td align=right>".hidezerodecimal($rtsatujjng,0)."</td>
            <td align=right>".hidezerodecimal($rtpersatu,2)."</td>
			<td align=right>".hidezerodecimal($rtduajjng,0)."</td>
            <td align=right>".hidezerodecimal($rtperdua,2)."</td>
			<td align=right>".hidezerodecimal($rttigajjng,0)."</td>
            <td align=right>".hidezerodecimal($rtpertiga,2)."</td>
			<td align=right>".hidezerodecimal($rtababnormaljjng,0)."</td>
            <td align=right>".hidezerodecimal($rtperababnormal,2)."</td>
			<td align=right>".hidezerodecimal($rtemptybunchjjng,0)."</td>
            <td align=right>".hidezerodecimal($rtperemptybunch,2)."</td>
			<td align=right>".hidezerodecimal($rtdurajjng,0)."</td>
            <td align=right>".hidezerodecimal($rtperdua,2)."</td>
			<td align=right>".hidezerodecimal($rttangkaipanjang,2)."</td>
			<td align=right>".hidezerodecimal($rtcomidel,2)."</td>
            <td align=right></td>
            <td align=right></td>
            <td align=right>".hidezerodecimal($rtperbrondol,2)."</td>
            <td align=right></td>
            <td align=right></td>";
			if($method=='html'){
				$tab.="<td colspan=2></td>";
			}
		$tab.="</tr>";
		
		$tab.="<tr class=rowcontent style='font-weight:bold'>
			<td colspan=3>TOTAL</td>
            <td align=right>".number_format($grdtotaljjg,0)."</td>
            <td align=right>".number_format($grdtonase,0)."</td>
            <td align=center colspan=2>".hidezerodecimal($rtpermentah,2)."</td>
            <td align=center colspan=6>".hidezerodecimal($grdfraksi,2)."</td>
            <td align=center colspan=2></td>
            <td align=center colspan=2></td>
            <td align=center colspan=2></td>
            <td align=center></td>
            <td align=center></td>
            <td align=right>".hidezerodecimal($grdtotalkgtbs,0)."</td>
            <td align=right>".hidezerodecimal($grdbrondolkg,0)."</td>
            <td align=center></td>
            <td align=center></td>
            <td align=center></td>";
			if($method=='html'){
				$tab.="<td colspan=2></td>";
			}
		$tab.="</tr>";
		
		$tab.="</table>
		</fieldset><br>";  
		
		if($method=='html'){
			echo $tab;
		}else{
			// $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			$nop_="Rekap_Sortasi_".$tipeexcel."_".$tglexcel;
			if(strlen($tab)>0)
			{
				if ($handle = opendir('tempFile')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempFile/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempFile/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab))
				{
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}
				else
				{
					echo "<script language=javascript1.2>
					window.location='tempFile/".$nop_.".xls';
					</script>";
				}
			}
		}
	break;
	
	case'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = str_replace(' ','',($newfilename."_".$tgl."".$filetype));
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 250000)
					{
						$newdata = array(
							'nofile'=>$data['nofile'],
							'namafile'=>$filename
						);
						
						if($_SESSION['sorimage'] != array())
						{
							foreach($_SESSION['sorimage'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['sorimage'],$newdata);
						}else{
							array_push($_SESSION['sorimage'],$newdata);
						}
						move_uploaded_file($file_tmpname,"fileupload/tempefil/$filename");
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
	break;
	
	case'loadfiles':
		$tab="";
		$no=0;
		foreach($_SESSION['sorimage'] as $key=>$row)
		{
			$nofile = checkPostGet('nofile', '');
			if($row['nofile']==$nofile){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td><a href='fileupload/tempefil/".$row['namafile']."' download>".substr($row['namafile'],0,30)."...</a></td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletefile('".$row['namafile']."','".$row['nofile']."')\" src='images/delete_32.png'>
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case 'deletefile':
		$namafile = checkPostGet('namafile', '');
		$nofile = checkPostGet('nofile', '');
		foreach($_SESSION['sorimage'] as $key=>$row)
		{
			if($row['namafile'] == $namafile && $row['nofile'] == $nofile)
			{
				$path = "fileupload/tempefil/".$namafile;
				unlink($path);
				unset($_SESSION['sorimage'][$key]);
			}
		}
	break;
}

function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

?>
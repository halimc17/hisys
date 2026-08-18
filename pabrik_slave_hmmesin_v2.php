<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/zFunction.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$caritanggal = checkPostGet('caritanggal', '');
$pages = checkPostGet('page', '');

$unit = checkPostGet('unit', '');
$tanggal = checkPostGet('tanggal', '');
$station = checkPostGet('station', '');

$substation = checkPostGet('substation', '');
$hour = checkPostGet('hour', '0');
$hmawal = checkPostGet('hmawal', '0');
$hmakhir = checkPostGet('hmakhir', '0');
$hournonpararel = checkPostGet('hournonpararel', '0');
$hourproses = checkPostGet('hourproses', '0');
$keterangan = checkPostGet('keterangan', '');
$current = checkPostGet('current', '');
$tp = checkPostGet('tp', '');

$app = 'pabrik';
$postJabatan = getPostingJabatan($app);
$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($proses)
{
	case'insertht':
		if($tp==''){
			## CEK INPUTAN
			$str="select count(substation) as jlhdata from ".$dbname.".pabrik_hmmesin where station='".$station."' and tanggal='".tanggalsystem($tanggal)."'";
			$res=fetchdata($str);
			if($res[0]['jlhdata'] > 0){
				exit('Gagal, data untuk station '.$optorg[$station].' untuk tanggal '.$tanggal.' sudah pernah diinput sebelumnya.');
			}
			
			$str="select count(substation) as jlhdata from ".$dbname.".pabrik_hmmesin where station='".$station."' and tanggal>'".tanggalsystem($tanggal)."'";
			$res=fetchdata($str);
			if($res[0]['jlhdata'] > 0){
				exit('Gagal, data untuk station '.$optorg[$station].' untuk tanggal yang lebih besar dari '.$tanggal.' sudah ada disistem, lakukan inputan lebih besar dari tanggal terakhir.');
			}
		}
	
		##Get array Station
		$str="select * from ".$dbname.".organisasi where induk='".$station."'";
		$arrstation=fetchData($str);
		$countstation = count($arrstation);
		
		## GET JAM OLAH
		$jamprocess=0;
		$str="select tanggal,jamdinasbruto from ".$dbname.".pabrik_pengolahan where tanggal = '".tanggalsystem($tanggal)."' and kodeorg='".$unit."' and posting='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			// $jamprocess+=$val['jamdinasbruto'];
		}
		
		## GET KEGIATAN
		$arrkegiatan=$arrnmkegiatan=array();
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'STENGINE' and induk like '".$station."%'";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrkegiatan[$val['kodeorganisasi']]=$val['kodeorganisasi'];
			$arrnmkegiatan[$val['kodeorganisasi']]=$val['namaorganisasi'];
		}
		
		## GET FROM MAINTENANCE
		$arrmaintenance=array();
		$str="select mesin from ".$dbname.".pabrik_rawatmesinht where tanggal='".tanggalsystem($tanggal)."' and pabrik='".$unit."' and statasiun='".$station."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrmaintenance[$val['mesin']]=1;
		}
		
		$tab.="<fieldset style=float:left;><legend>Detail</legend>
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
				<tr align=center>
					<td rowspan=2>".$_SESSION['lang']['nourut']."</td>
					<td rowspan=2>".$_SESSION['lang']['kode']."</td>
					<td rowspan=2>Sub ".$_SESSION['lang']['station']."</td>
					<td rowspan=2 hidden>Hour<br>Paralel</td>
					<td rowspan=2 hidden>Hour<br>Non-Paralel</td>
					<td colspan=2 hidden>".$_SESSION['lang']['data']." ".$_SESSION['lang']['awal']."</td>
					<td colspan=3>".$_SESSION['lang']['data']." HM</td>
					<td rowspan=2>
						<input type=checkbox id=allCheck onclick=checkAll('".count($arrnmkegiatan)."') checked />
					</td>
					<td rowspan=2>".$_SESSION['lang']['keterangan']."</td>
				</tr>
				<tr align=center>
					<td hidden>".$_SESSION['lang']['awal']."</td>
					<td hidden>".$_SESSION['lang']['akhir']."</td>

					<td>".$_SESSION['lang']['awal']."</td>
					<td>Process</td>
					<td>".$_SESSION['lang']['akhir']."</td>
				</tr>
				</thead>
				<tbody>";
				$no=0;
				$nox=0;
				// foreach($arrstation as $key=>$val){	
					// $no++;
					// @$optsubstt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeorganisasi']."'");
				
					// $tab.="<tr class=rowcontent style='font-weight:bold'>";
					// $tab.="<td style='text-align:right'>".$no."</td>";
					// $tab.="<td style='text-align:left'>".$val['kodeorganisasi']."</td>";
					// $tab.="<td style='text-align:left'>".$optsubstt[$val['kodeorganisasi']]."</td>";

					// ## HM DATA PERBAIKAN
					// $tab.="<td style='text-align:center'></td>";
					// $tab.="<td style='text-align:center'></td>";
					// $tab.="<td style='text-align:center'></td>";
					// $tab.="<td style='text-align:center'></td>";

					// ## KETERANGAN
					// $tab.="<td style='text-align:left'></td>";
					// $tab.="</tr>";
					
					foreach($arrkegiatan as $keyx=>$valx){
						$nox++;
						
						$strx="select * from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".tanggalsystem($tanggal)."' and station='".$station."' and substation='".$valx."'";
						$resx=fetchData($strx);
						@$hourprosesx = ($resx[0]['hourproses']==''?0:$resx[0]['hourproses']);
						@$hmawal = ($resx[0]['hmawal']==''?0:$resx[0]['hmawal']);
						@$hmakhir = ($resx[0]['hmakhir']==''?0:$resx[0]['hmakhir']);
						
						$strCek="select * from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal<'".tanggalsystem($tanggal)."' and station='".$station."' and substation='".$valx."' order by tanggal desc limit 1";
						$resCek=fetchData($strCek);
						$ctCek=count($resCek);
						$hmawalNew=($resCek[0]['hmawal']==''?0:$resCek[0]['hmawal']);
						$hmakhirNew=($resCek[0]['hmakhir']==''?0:$resCek[0]['hmakhir']);
						
						if($tp==''){
							$hmawal=$hmakhirNew;
						}
						if($hourprosesx > 0){
							$jamprocess=$hourprosesx;
						}else{
							$jamprocess=0;
						}
						if($hmakhir <= 0){
							// $hmakhir=$hmawal+$jamprocess;							
							$hmakhir=$hmawal;							
						}

						$dsbled="";
						if($arrmaintenance[$keyx]=='1'){
							// $dsbled="disabled";
							// $hmakhir=0;
							// $keteranganx="reset auto from maintenance";
						}
						
						$tab.="<tr class=rowcontent id='tr_".$nox."'>";
						$tab.="<td style='text-align:right'>".$nox."</td>";
						$tab.="<td style='text-align:right' id='subkodemesin_".$nox."'>".$keyx."</td>";
						$tab.="<td style='text-align:left'>".$arrnmkegiatan[$keyx]."</td>";

						## HM DATA PERBAIKAN
						$tab.="<td style='text-align:center'>
							<input type='text' size='3' id='hmawal_".$nox."' class='myinputtextnumber' onkeypress='return angka_doang(event)' value='".$hmawal."'  onkeyup=\"gethour('".$nox."')\">
							<input type='hidden' id='substation_".$nox."' value='".$valx."'>
						</td>";
						$tab.="<td style='text-align:center'>
							<input type='text' size='3'  id='hourproses_".$nox."' class='myinputtextnumber' onkeypress='return angka_doang(event)' value='".$jamprocess."' disabled>
						</td>";
						$tab.="<td style='text-align:center'>
							<input type='text' size='3' id='hmakhir_".$nox."' class='myinputtextnumber' ".$dsbled." onkeypress='return angka_doang(event)' value='".$hmakhir."'onkeyup=\"gethour('".$nox."')\">
						</td>";
						
						$tab.="<td style='text-align:center'>
							<input type=checkbox id='hmchecked_".$nox."' onclick=checkone('".$nox."') ".$dsbled." checked/>
						</td>";

						## KETERANGAN
						$tab.="<td style='text-align:left'>
							<input  type='text' class='myinputtext' id='keterangan_".$nox."' onkeypress=\"return tanpa_kutip(event);\" style='width:250px;' value='".$keteranganx."' ".$dsbled." />
						</td>";
						$tab.="</tr>";
					}
				// }
			$tab.="<tr>
				<td colspan=8 style='text-align:center'>
					<button class=mybutton id='simpanht' onclick=savedt('".count($arrnmkegiatan)."')>".$_SESSION['lang']['save']."</button>&nbsp;
					<button class=mybutton id='cancelht' onclick=canceldt()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>				
			</tbody>
			</table>
		</fieldset>";
		echo $tab;
	break;
	
	case'savedt':
		if($current=='1'){
			$str="delete from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".tanggalsystem($tanggal)."' and station='".$station."'";
			try{$owlPDO->exec($str);}catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n"; die();}
		}
		
		$strCek="select * from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal<'".tanggalsystem($tanggal)."' and station='".$station."' and substation='".$substation."' and hmawal!='0'  order by tanggal desc limit 1";
		// exit("Warning:".$strCek);
		$resCek=fetchData($strCek);
		$ctCek=count($resCek);
		$hmawalNew=$resCek[0]['hmawal'];
		$hmakhirNew[$unit]=$resCek[0]['hmakhir'];
		if ($ctCek>0) {
			if ($hmakhirNew[$unit]>$hmawal) {
				exit("Warning: HM AWAL tidak boleh lebih kecil dari tanggal sebelummnya!!");
			}
		}
		// if(($hour=='0' or $hour=='') and $keterangan==''){
			
		// }else{
			$str="insert into ".$dbname.".pabrik_hmmesin  (unit,tanggal,station,substation,hour,hmawal,hmakhir,hournonpararel,hourproses,keterangan,createdby,createdtime,updateby,updatetime) 
			values ('".$unit."','".tanggalsystem($tanggal)."','".$station."','".$substation."','".$hour."','".$hmawal."','".$hmakhir."','".$hournonpararel."','".$hourproses."','".$keterangan."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try{$owlPDO->exec($str);}catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n"; die();}
		// }
	break;
	
	case'loadData':
		$arrorgdet  = getOrgDetail(2);
        $colspan    = 20;
		$where      = " and unit in (".$arrorgdet.")";
		//Inisialisasi Search
		if($caritanggal!='')
		{
			$caritanggal = substr($caritanggal,6,4)."-".substr($caritanggal,3,2)."-".substr($caritanggal,0,2);
			$where.=" and tanggal like '".$caritanggal."%'";
        }
	
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		@$offset=@$page*@$limit;
        
		$str="select * from ".$dbname.".pabrik_hmmesin where 1=1 ".$where." group by tanggal,station";
        $hsl=count(fetchData($str));
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$jlhbrs= $hsl;	
		}
		
		$tab='';
		$nor=0;
		
		$str="select * from ".$dbname.".pabrik_hmmesin where 1=1 ".$where." group by station,tanggal order by tanggal DESC limit ".$offset.",".$limit." ";
        if(count(fetchData($str)) > 0){
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch())
            {
                $nor+=1;
                
                $optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['unit']."'");
                $optStation = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['station']."'");
                $optKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
                
                $tab.="<tr class=rowcontent>
                    <td id='nor_".$nor."' value='".$nor."'>".$bar['unit']." - ".$optUnit[$bar['unit']]."</td>
                    <td style='text-align:center'>".tanggalnormal($bar['tanggal'])."</td>
                    <td id='nor_".$nor."' value='".$nor."'>".$bar['station']." - ".$optStation[$bar['station']]."</td>
                    <td>".$optKaryawan[$bar['updateby']]."</td>";
                
                    if($bar['status']=='0')
                    {
                        $tab.="<td style='text-align:center'>Created</td>";
                        $tab.="<td style='text-align:center'>
                            <img src=images/application/application_edit.png class=zImgBtn title='edit' onclick=\"editall('".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['station']."');\">
                        </td>";
                        $tab.="<td style='text-align:center'>
                            <img src=images/application/application_delete.png class=zImgBtn title='delete' onclick=\"deleteall('".$bar['unit']."','".$bar['tanggal']."','".$bar['station']."');\">
                        </td>";
                        $tab.="<td style='text-align:center'>
                            <img src=images/skyblue/posting.png class=zImgBtn title='Posting ?' onclick=\"postall('".$bar['unit']."','".$bar['tanggal']."','".$bar['station']."');\">
                        </td>";
                    }
                    else
                    {
                        $tab.="<td style='text-align:center'>Posted</td>";
                        $tab.="<td style='text-align:center'></td>";
                        $tab.="<td style='text-align:center'></td>";
                        if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
                            $tab.="<td style='text-align:center'>
                                <img src=images/icons/04/16/02.png class=zImgBtn title='Unposted' style='cursor:pointer' onclick=\"unpostall('".$bar['unit']."','".$bar['tanggal']."','".$bar['station']."');\">
                            </td>";
                        }else{
                            $tab.="<td style='text-align:center'>
                                <img src=images/icons/04/16/02.png class=zImgOffBtn title='Posted'>
                            </td>";
                        }
                    }
                $tab.="<td style='text-align:center'>
                    <img src=images/zoom.png class=zImgBtn title='print' onclick=\"showdetail('".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['station']."',event);\">
                </td>
                </tr>";
            }
        }else{
            $tab.="<tr class=rowcontent><td align=center colspan=".$colspan.">".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }
		## PAGING
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage');
		echo $tab."####".$footd;
	break;
	
	case'deleteall':
		$str="delete from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".$tanggal."' and station='".$station."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	
	case'showdetail':
		$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optStation = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$station."'");
	
		##Get array Station
		$str="select * from ".$dbname.".organisasi where induk='".$station."'";
		$arrstation=fetchData($str);
		$countstation = count($arrstation);
	
		$tab="";
		$tab.="<link rel=stylesheet type='text/css' href='style/".$gen."'>";
		$tab.="<fieldset>
			<table style='font-size: 12px;'>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td>".$unit."-".$optUnit[$unit]."</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td>".($tanggal)."</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>
					<td>".$station."-".$optStation[$station]."</td>
				</tr>
			</table>
			<table cellpadding=3 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
				<tr align=center>
					<td rowspan=2>".$_SESSION['lang']['nourut']."</td>
					<td rowspan=2>".$_SESSION['lang']['kode']."</td>
					<td rowspan=2>Sub ".$_SESSION['lang']['station']."</td>
					<td colspan=3>".$_SESSION['lang']['data']." HM</td>
					<td rowspan=2>".$_SESSION['lang']['keterangan']."</td>
				</tr>
				<tr align=center>
					<td>".$_SESSION['lang']['awal']."</td>
					<td>Process</td>
					<td>".$_SESSION['lang']['akhir']."</td>
				</tr>
				
				</thead>
				
				<tbody>";
				## GET KEGIATAN
				$arrkegiatan=$arrnmkegiatan=array();
				$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'STENGINE' and induk like '".$station."%'";
				$res=fetchdata($str);
				foreach($res as $val){
					$arrkegiatan[$val['kodeorganisasi']]=$val['kodeorganisasi'];
					$arrnmkegiatan[$val['kodeorganisasi']]=$val['namaorganisasi'];
				}
				
				$no=0;
				// foreach($arrstation as $key=>$val){					
				// 	$no++;
				// 	$optsubstt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeorganisasi']."'");

					// $tab.="<tr class=rowcontent style='font-weight:bold'>";
					// $tab.="<td style='text-align:right'>".$no."</td>";
					// $tab.="<td style='text-align:left'>".$val['kodeorganisasi']."</td>";
					// $tab.="<td style='text-align:left'>".$optsubstt[$val['kodeorganisasi']]."</td>";
					// $tab.="<td style='text-align:right'></td>";
					// $tab.="<td style='text-align:right'></td>";
					// $tab.="<td style='text-align:right'></td>";
					// $tab.="<td style='text-align:right'></td>";
					// $tab.="</tr>";
					
					foreach($arrkegiatan as $keyx=>$valx){
						$strx="select * from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".tanggalsystem($tanggal)."' and station='".$station."' and substation='".$valx."'";
						$resx=fetchData($strx);
						@$hmawal = ($resx[0]['hmawal']==''?0:$resx[0]['hmawal']);
						@$hmakhir = ($resx[0]['hmakhir']==''?0:$resx[0]['hmakhir']);
						@$hourprosesx = $resx[0]['hourproses'];
						@$keteranganx = $resx[0]['keterangan'];
						
						$tab.="<tr class=rowcontent>";
						$tab.="<td style='text-align:right'>".@$nox++."</td>";
						$tab.="<td style='text-align:right'>".$keyx."</td>";
						$tab.="<td style='text-align:left'>".$arrnmkegiatan[$keyx]."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($hmawal,10)."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($hourprosesx,10)."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($hmakhir,10)."</td>";
						$tab.="<td style='text-align:left'>".$keteranganx."</td>";
						$tab.="</tr>";
					}
				// }				
			$tab.="</tbody>
			</table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'postall':
		$str="update ".$dbname.".pabrik_hmmesin set status='1',postedby='".$_SESSION['standard']['userid']."' where unit='".$unit."' and tanggal='".$tanggal."' and station='".$station."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	
	case'unpostall':
		$str="update ".$dbname.".pabrik_hmmesin set status='0',postedby='0' where unit='".$unit."' and tanggal='".$tanggal."' and station='".$station."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
}
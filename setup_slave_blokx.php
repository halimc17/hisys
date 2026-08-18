<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   	= checkPostGet('method', '');
$param 		= $_POST;if(count($param)==0){$param = $_GET;}
$arrbulan	= array(
				'1'		=> 'Januari',
				'2'		=> 'Februari',
				'3'		=> 'Maret',
				'4'		=> 'April',
				'5'		=> 'Mei',
				'6'		=> 'Juni',
				'7'		=> 'Juli',
				'8'		=> 'Agustus',
				'9'		=> 'September',
				'10'	=> 'Oktober',
				'11'	=> 'November',
				'12'	=> 'Desember');
switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$str = "select count(*) as jlh  from " . $dbname . ".keu_jurnaldt where kodeblok='".$param['kodeorg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode blok sudah dipakai di jurnal.");
				}
			}
			$str = "select count(*) as jlh  from " . $dbname . ".kebun_prestasi where kodeorg='".$param['kodeorg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode blok sudah dipakai di BKM.");
				}
			}
			$str = "select count(*) as jlh  from " . $dbname . ".log_transaksidt where kodeblok='".$param['kodeorg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode blok sudah dipakai di Gudang.");
				}
			}
			$str = "select count(*) as jlh  from " . $dbname . ".vhc_rundt where alokasibiaya='".$param['kodeorg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode blok sudah dipakai di Traksi.");
				}
			}
			$str = "select count(*) as jlh  from " . $dbname . ".lgl_pengajuanspk_keg where subunit='".$param['kodeorg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode blok sudah dipakai di SPK.");
				}
			}
			
			
			$where = " kodeorg='".$param['kodeorg']."'";
			$str = "delete from " . $dbname . ".setup_blok where ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'update':
		if(($param['statusblok'] == 'TM' or $param['statusblok'] == 'TBM') and $param['basisbuah'] == '2'  ){
			exit("Warning : Jika status blok TM/TBM wajib isi basis buah kecil/besar ");
		}

		
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'tahuntanam'           => $param['tahuntanam'],
					'buahkecil'            => $param['basisbuah'],
					'luasareaproduktif'    => $param['luas'],
					'luasareanonproduktif' => $param['luasareanonproduktif'],
					'jumlahpokok'          => $param['pokok'],
					'statusblok'           => $param['statusblok'],
					'tahunmulaipanen'      => $param['tahunmulaipanen'],
					'bulanmulaipanen'      => $param['bulanmulaipanen'],
					'kodetanah'            => $param['kodetanah'],
					'klasifikasitanah'     => $param['klasifikasitanah'],
					'topografi'            => $param['topografi'],
					'jenisbibit'           => $param['jenisbibit'],
					'intiplasma'           => $param['intiplasma'],
					'cadangan'             => $param['cadangan'],
					'arealberbatu'         => $param['arealberbatu'],
					'konservasi'           => $param['konservasi'],
					'enclave'              => $param['enclave'],
					'okupasi'              => $param['okupasi'],
					'rendahan'             => $param['rendahan'],
					'sungai'               => $param['sungai'],
					'rumah'                => $param['rumah'],
					'kantor'               => $param['kantor'],
					'pabrik'               => $param['pabrik'],
					'jalan'                => $param['jalan'],
					'kolam'                => $param['kolam'],
					'umum'                 => $param['umum'],
					'lc'                   => $param['lc'],
					'luasbloking'          => $param['luasbloking'],
					'status'               => $param['status']
				);
				$where = "kodeorg='".$param['blok']."'";
				$query = updateQuery($dbname,'setup_blok',$data,$where); //exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':

		if(($param['statusblok'] == 'TM' or $param['statusblok'] == 'TBM') and $param['basisbuah'] == '2'  ){
			exit("Warning : Jika status blok TM/TBM wajib isi basis buah kecil/besar ");
		}

		try {
			$owlPDO->beginTransaction();
			$data = array(
				'kodeorg'              => $param['blok'],
				'indukblok'            => $param['indukblok'],
				'tahuntanam'           => $param['tahuntanam'],
				'buahkecil'            => $param['basisbuah'],
				'luasareaproduktif'    => $param['luas'],
				'luasareanonproduktif' => $param['luasareanonproduktif'],
				'jumlahpokok'          => $param['pokok'],
				'statusblok'           => $param['statusblok'],
				'tahunmulaipanen'      => $param['tahunmulaipanen'],
				'bulanmulaipanen'      => $param['bulanmulaipanen'],
				'kodetanah'            => $param['kodetanah'],
				'klasifikasitanah'     => $param['klasifikasitanah'],
				'topografi'            => $param['topografi'],
				'jenisbibit'           => $param['jenisbibit'],
				'intiplasma'           => $param['intiplasma'],
				'cadangan'             => $param['cadangan'],
				'arealberbatu'         => $param['arealberbatu'],
				'konservasi'           => $param['konservasi'],
				'enclave'              => $param['enclave'],
				'okupasi'              => $param['okupasi'],
				'rendahan'             => $param['rendahan'],
				'sungai'               => $param['sungai'],
				'rumah'                => $param['rumah'],
				'kantor'               => $param['kantor'],
				'pabrik'               => $param['pabrik'],
				'jalan'                => $param['jalan'],
				'kolam'                => $param['kolam'],
				'umum'                 => $param['umum'],
				'lc'                   => $param['lc'],
				'luasbloking'          => $param['luasbloking'],
				'status'               => $param['status']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'setup_blok',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'getblok':
		$blok=[];
		$str = "select *  from " . $dbname . ".setup_blok where kodeorg like '".$param['indukblok']."%'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$blok[$bar['kodeorg']]=$bar['kodeorg'];
		}
	
		$optblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".`organisasi` where indukblok = '".$param['indukblok']."' and tipe='BLOK'";
		$res = fetchdata($str);
		foreach($res as $val){
			$disabled="";
			if($blok[$val['kodeorganisasi']]!=''){
				$disabled="disabled";
			}
			$optblok.="<option value=".$val['kodeorganisasi']." ".$disabled.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}
		
		echo $optblok;
	break;	

	case 'getindukblok':
	
		$optblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".`organisasi` where induk = '".$param['divisi']."' and tipe='BLOK' group by indukblok";
		$res = fetchdata($str);
		foreach($res as $val){
			$optblok.="<option value=".$val['indukblok'].">".$val['indukblok']." - ".$val['namaindukblok']."</option>";			
		}
		
		echo $optblok;
	break;
	
	case 'addnew':

		$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

		$arrUnit = getOrgDetail(23);
		foreach($arrUnit as $key=>$val){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$optkodeorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			
			$optkodeorg.="<option value='".$key."'>".$key." - ".$val."</option>";			
			
			$n=$d;
			if($d!=$n){			
				$optkodeorg.="</optgroup>";
			}
		}
		
		$optblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optindukblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optdiv="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach(getOrgDetail(19) as $key => $val){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$optdiv.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optdiv.="<option value=".$key." ".$s.">".$key." - ".$val."</option>";
			$n=$d;
			if($d!=$n){			
				$optdiv.="</optgroup>";
			}
		}
		
		$tmpJenisTanah = readLst("./config/jenistanah.lst");
		$optJenisTanah = lst2opt($tmpJenisTanah,0,1);
		// $tmpKlsTanah   = readLst("./config/kelastanah.lst");
		// $optKlsTanah   = lst2opt($tmpKlsTanah,0,1);
		$optKlsTanah = makeOption($dbname,'setup_kelaslahan','kode,nama', 'aktif=1','2');

		$optBlokStat   = getEnum($dbname,'setup_blok','statusblok');
		$optIP         = getEnum($dbname,'setup_blok','intiplasma');
		$optAD         = getEnum($dbname,'setup_blok','status');
		$optTopografi  = makeOption($dbname,'setup_topografi','topografi,keterangan');
		
		//$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($optBlokStat as $row) {
			$optstatus.="<option value=".$row.">".$row."</option>";
		}
		$optkodetanah="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($optJenisTanah as $row) {
			$optkodetanah.="<option value=".$row.">".$row."</option>";
		}
		$optklstanah="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($optKlsTanah as $row) {
			$optklstanah.="<option value=".substr($row,0,2).">".$row."</option>";
		}
		//$optplasma="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($optIP as $row) {
			$optplasma.="<option value=".$row.">".$row."</option>";
		}
		//$optstatusd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($optAD as $row) {
			$optstatusd.="<option value=".$row.">".$row."</option>";
		}
		$opttopo="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".setup_topografi";
		$res = fetchData($str);
		foreach($res as $val){
			$opttopo.="<option value=".$val['topografi'].">".$val['topografi']." - ".$val['keterangan']."</option>";
		}
		$optbibit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".setup_jenisbibit";
		$res = fetchData($str);
		foreach($res as $val){
			$optbibit.="<option value=".$val['jenisbibit'].">".$val['jenisbibit']."</option>";
		}

		$arrBasis = array('0'=>'Basis Buah Besar','1'=>'Basis Buah Kecil','2'=>'Tanpa Basis');
		foreach($arrBasis as $brs => $isi){
			$optBasis.="<option value=".$brs.">".$isi."</option>";
		}
		
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:250px>".$_SESSION['lang']['kebun']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td style=min-width:300px><select class='select2' style='width:250px;' onchange=getdivisi2x('divisi',this.value,'pilihdata'); id=kodeorg >".$optkodeorg."</select></td>
				
					<td style=min-width:250px>".$_SESSION['lang']['divisi']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td><select class='select2' style='width:250px;' onchange=getindukblok(this.value); id=divisi >".$optdiv."</select></td>
				</tr>
				<tr>

					<td style=min-width:250px>Induk ".$_SESSION['lang']['blok']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td><select class='select2' style='width:250px;' onchange=getblok(this.value); id=indukblok >".$optindukblok."</select></td>

					<td>".$_SESSION['lang']['blok']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td><select class='select2' style='width:250px;' id=blok >".$optblok."</select></td>
				
				</tr>
				<tr>

				
					<td>".$_SESSION['lang']['tahuntanam']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='tahuntanam' onkeypress=\"return isNumberKey2(event);\" maxlength=4 placeholder=0></td>

					<td>".$_SESSION['lang']['luas']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='luas' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
			
				</tr>
				<tr>
				
					<td>".$_SESSION['lang']['pokok']."</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='pokok' onkeypress=\"return isNumberKey2(event);\" placeholder=0></td>

					<td>".$_SESSION['lang']['statusblok']." <font size=2px style='color:red;vertical-align:middle;vertical-align:middle'><b>*</b></font></td>
					<td><select class='select2' style='width:250px;' id=statusblok >".$optstatus."</select></td>
				
				</tr>
				<tr>

					<td>".$_SESSION['lang']['kodetanah']."</td>
					<td><select class='select2' style='width:250px;' id=kodetanah >".$optkodetanah."</select></td>
					
					<td>Tahun Mulai Panen</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='tahunmulaipanen' onkeypress=\"return isNumberKey(event);\" name=tahunmulaipanen[] onclick=delnol(this); placeholder=0 maxlength=4></td>
				
				</tr>
				<tr>

					<td>Bulan Mulai Panen</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='bulanmulaipanen' onkeypress=\"return isNumberKey(event);\" name=bulanmulaipanen[] onclick=delnol(this); placeholder=0 maxlength=2></td>

					<td>".$_SESSION['lang']['klasifikasitanah']."</td>
					<td><select class='select2' style='width:250px;' id=klasifikasitanah >".$optklstanah."</select></td>
				
				</tr>
				<tr>

				
					<td>".$_SESSION['lang']['topografi']."</td>
					<td><select class='select2' style='width:250px;' id=topografi >".$opttopo."</select></td>

					<td>".$_SESSION['lang']['intiplasma']."</td>
					<td><select class='select2' style='width:250px;' id=intiplasma >".$optplasma."</select></td>
			
				</tr>
				<tr>

					<td>".$_SESSION['lang']['jenisbibit']."</td>
					<td><select class='select2' style='width:250px;' id=jenisbibit >".$optbibit."</select></td>

					<td>Basis Buah</td>
					<td><select class='select2' style='width:250px;' id=basisbuah >".$optBasis."</select></td>

				</tr>
				<tr>

					<td>Bloking</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='luasbloking' onkeypress=\"return isNumberKey(event);\" placeholder=0 ></td>
			
					<td>".$_SESSION['lang']['luasareanonproduktif']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='luasareanonproduktif' onkeypress=\"return isNumberKey(event);\" placeholder=0 disabled></td>
					
				</tr>
				<tr>

				
					<td>".$_SESSION['lang']['cadangan']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='cadangan' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>

					<td>".$_SESSION['lang']['okupasi']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='okupasi' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
				</tr>
				<tr>
					<td>".$_SESSION['lang']['sungai']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='sungai' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
					<td>".$_SESSION['lang']['rumah']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='rumah' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kantor']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='kantor' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='pabrik' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jalan']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='jalan' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
					<td>".$_SESSION['lang']['kolam']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='kolam' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['umum']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='umum' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
					<td>".$_SESSION['lang']['arealberbatu']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='arealberbatu' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['konservasi']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='konservasi' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
					<td>Enclave</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='enclave' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
				</tr>
				<tr>
					<td>LC</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='lc' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
					
					<td>".$_SESSION['lang']['status']."</td>
					<td><select class='select2' style='width:250px;' id=status>".$optstatusd."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['rendahan']."</td>
					<td><input class=myinputtextnumber style='text-align:right;width:245px;height:30px;font-size:14px;' type=text id='rendahan' onkeypress=\"return isNumberKey(event);\" name=nonprd[] onclick=delnol(this); onkeyup=gettotalnonprd(); placeholder=0></td>
				</tr>
				<tr hidden>
					<td style=width:160px>".$_SESSION['lang']['blok']." ".$_SESSION['lang']['lama']."</td>
					<td><input style='text-align:right;width:245px;height:30px;font-size:14px;' type=text  type=text id=blokold size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=8></td>
				</tr>
                <tr>
                    <td>&nbsp;</td>
				</tr>
                <tr>
                    <td colspan=5 align=center>
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'></th>
				<th style='text-align:center;'></th>
				<th style='text-align:center;'></th>
				<th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kodeblok']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['namablok']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['tahuntanam']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['luas']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['pokok']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['statusblok']."</th>
				<th style='text-align:center;'>Tahun Mulai Panen</th>
				<th style='text-align:center;'>Bulan Mulai Panen</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kodetanah']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['klasifikasitanah']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['topografi']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['intiplasma']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['jenisbibit']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['luasareanonproduktif']."</th>
				<th style='text-align:center;'>Induk Blok</th>
				<th style='text-align:center;'>Basis Buah</th>
				<th style='text-align:center;'>Luas Bloking</th>
				<th style='text-align:center;'>".$_SESSION['lang']['cadangan']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['okupasi']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['rendahan']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['sungai']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['rumah']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kantor']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['pabrik']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['jalan']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kolam']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['umum']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['arealberbatu']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['konservasi']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['enclave']."</th>
				<th style='text-align:center;'>LC</th>
				<th style='text-align:center;'>".$_SESSION['lang']['status']."</th>
			</tr>
		</thead>
		<tbody >";
		
		$tmpJenisTanah = readLst("./config/jenistanah.lst");
		$optJenisTanah = lst2opt($tmpJenisTanah,0,1);
		$tmpKlsTanah   = readLst("./config/kelastanah.lst");
		$optKlsTanah   = lst2opt($tmpKlsTanah,0,1);
		$optBlokStat   = getEnum($dbname,'setup_blok','statusblok');
		$optIP         = getEnum($dbname,'setup_blok','intiplasma');
		$optTopografi  = makeOption($dbname,'setup_topografi','topografi,keterangan');

		$arrBasis = array('0'=>'Basis Buah Besar','1'=>'Basis Buah Kecil','2'=>'Tanpa Basis');

		
		// echo"<pre>";
		// print_r($optJenisTanah);
		// print_r($optKlsTanah);
		// print_r($optBlokStat);
		// print_r($optTopografi);
		
		// print_r(getOrgDetail(26));
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
		// 	$tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'");
		// } elseif($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
		// 	$tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
		// } else {
		// 	$tmpOpt = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebunonly');
		// }

		// foreach($tmpOpt as $key=>$row) {
		// 	$orgdet[$key]=$key;
		// }
		
		// $where=" and substr(kodeorg,1,4) in ('".implode("','",$orgdet)."')";
		$str = "select * from ".$dbname.".setup_blok where substr(kodeorg,1,4) in (".getOrgDetail(2).") ".$where." order by kodeorg, tahuntanam asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$e = substr($bar['kodeorg'],0,4);
			if($e!=$o){
				$no+=1;
				$tab.="<tr class=rowcontent style=background-color:#e8e8e8>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td style='text-align:center;'>".$no."</td>";
				$tab.="<td></td>";
				$tab.="<td style='text-align:left;'>".$e."</td>";
				$tab.="<td style='text-align:left;'>".getNamaOrg($e)."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td><td></td><td></td><td></td>";
				$tab.="<td></td>";
				// $tab.="<td></td>";
				$tab.="</tr>";
			}
			
			$d=substr($bar['kodeorg'],0,6);
			if($d!=$n){
				$no+=1;
				$tab.="<tr class=rowcontent style=background-color:#e8e8e8>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td style='text-align:center;'>".$no."</td>";
				$tab.="<td></td>";
				$tab.="<td style='text-align:left;'>".$d."</td>";
				$tab.="<td style='text-align:left;'>".getNamaOrg($d)."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td><td></td><td></td><td></td>";
				$tab.="<td></td>";
				// $tab.="<td></td>";
				$tab.="</tr>";
			}
			
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td></td>";
			$tab.="<td style='text-align:center;width:25px'><img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".substr($bar['kodeorg'],0,4)."','".substr($bar['kodeorg'],0,6)."','".$bar['kodeorg']."','".$bar['tahuntanam']."','".$bar['indukblok']."','".$bar['luasareaproduktif']."','".$bar['jumlahpokok']."','".$bar['statusblok']."','".$bar['kodetanah']."','".$bar['klasifikasitanah']."','".$bar['topografi']."','".$bar['intiplasma']."','".$bar['jenisbibit']."','".$bar['luasareanonproduktif']."','".$bar['cadangan']."','".$bar['okupasi']."','".$bar['rendahan']."','".$bar['sungai']."','".$bar['rumah']."','".$bar['kantor']."','".$bar['pabrik']."','".$bar['jalan']."','".$bar['kolam']."','".$bar['umum']."','".$bar['arealberbatu']."','".$bar['konservasi']."','".$bar['enclave']."','".$bar['status']."','".$bar['lc']."','".$bar['tahunmulaipanen']."','".$bar['bulanmulaipanen']."','".$bar['blokold']."','".$bar['buahkecil']."','".$bar['luasbloking']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px'><img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['kodeorg']."');></td>";
				
			$tab.="<td style='text-align:center;'>".$no."</td>";
			// $tab.="<td hidden style='text-align:left;'>".$bar['blokold']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['kodeorg']."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['tahuntanam']."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['luasareaproduktif'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jumlahpokok'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['statusblok']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['tahunmulaipanen']."</td>";
			$tab.="<td style='text-align:center;'>".$arrbulan[$bar['bulanmulaipanen']]."</td>";
			$tab.="<td style='text-align:left;'>".$bar['kodetanah']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['klasifikasitanah']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['topografi']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['intiplasma']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['jenisbibit']."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['luasareanonproduktif'],2)."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($bar['indukblok'])."</td>";
			$tab.="<td style='text-align:left;'>".$arrBasis[$bar['buahkecil']]."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['luasbloking'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['cadangan'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['okupasi'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rendahan'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['sungai'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rumah'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['kantor'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['pabrik'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jalan'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['kolam'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['umum'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['arealberbatu'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['konservasi'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['enclave'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['lc'],2)."</td>";
			$tab.="<td style='text-align:center;'>".$bar['status']."</td>";
			
			
					
			$tab.="</tr>";

			$n=$d;
			$o=$e;
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
}
?>

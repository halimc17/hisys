<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);
$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

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

$arrjenis=array('0'=>'Header','1'=>'Sub Header','2'=>'KPI Text');
$arrtipepenilaian=array('0'=>'Umum','1'=>'Berdasarkan Skala Penilaian');
$arrtext=makeOption($dbname, 'sdm_5kpi', 'id,kpi',"jenis in ('0','1')");;
// print_r($param);
// exit('ERROR');
switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " and id='".$param['id']."'";
			$str = "delete from " . $dbname . ".sdm_5kpi where 1=1 ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'getinduk':
		$optinduk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5kpi where jenis in ('0','1') and jabatan='".$param['jabatan']."' and kodeorg='".$param['kodeorg']."'  and tahun='".$param['tahun']."'   order by kpi asc";
		$res = fetchData($str);
		foreach($res as $val){
			$optinduk.="<option value=".$val['id'].">".$val['kpi']."</option>";			
		}
		echo $optinduk;
		
	break;

	case 'getskala':
		$optskala="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5setupscore where status='1' group by judul order by judul asc";
		$res = fetchData($str);
		foreach($res as $val){
			$optskala.="<option value=".$val['judul'].">".$val['judul']."</option>";			
		}
		echo $optskala;
		
	break;
	case 'update':
		try {
			$owlPDO->beginTransaction();
				if($param['target']==0){
					throw new PDOException("Jumlah target tidak boleh 0.");
				}
				$param['kpi'] = str_replace('"',"",$param['kpi']);
				$param['kpi'] = trim($param['kpi']);
				$data = array(
					'tahun'       => $param['tahun'],
					'jabatan'     => $param['jabatan'],
					'karyawanid'     => $param['karyawanid'],
					'dept'        => $param['dept'],
					'kpi'         => $param['kpi'],
					'bobot'       => $param['bobot'],
					'kodeorg'     => $param['kodeorg'],
					'divisi'      => $param['divisi'],
					'target'	  => $param['target'],
					'jenis' 	       => $param['jenis'],
					'induk' 	  		  => $param['induk'],
					'tipepenilaian' 	  => $param['tipepenilaian'],
					'penilaian' 	  => $param['skalapenilaian'],
					'updateby'    => $_SESSION['standard']['userid']

				);
				$where = "id='".$param['idkpi']."'";
				$query = updateQuery($dbname,'sdm_5kpi',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			if($param['target']==0 and ($param['jenis']!=0 and $param['jenis']!=1)){
					throw new PDOException("Jumlah target tidak boleh 0.");
			}
			$param['kpi'] = str_replace('"',"",$param['kpi']);
			$param['kpi'] = trim($param['kpi']);
			$data = array(
				'tahun'       => $param['tahun'],
				'jabatan'     => $param['jabatan'],
				'karyawanid'     => $param['karyawanid'],
				'dept'        => $param['dept'],
				'kpi'         => $param['kpi'],
				'bobot'       => $param['bobot'],
				'kodeorg'     => $param['kodeorg'],
				'divisi'      => $param['divisi'],
					'target'	  => $param['target'],
					'jenis' 	       => $param['jenis'],
					'induk' 	  		  => $param['induk'],
					'tipepenilaian' 	  => $param['tipepenilaian'],
					'penilaian' 	  => $param['skalapenilaian'],
				'createdby'   => $_SESSION['standard']['userid'],
				'createdtime' => date("Y-m-d H:i:s"),
				'updateby'    => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'sdm_5kpi',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'getdivisi':
		$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and induk='".$param['kodeorg']."' order by induk, tipe";
		$res = fetchData($str);
		foreach($res as $key){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key['kodeorganisasi']."'");
			$d=$induk[$key['kodeorganisasi']];
			if($d!=$n){			
				$optdivisi.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$s="";
			if($key['kodeorganisasi']==$param['divisi']){
				$s="selected";
			}
			
			$optdivisi.="<option value=".$key['kodeorganisasi']." ".$s.">".$key['kodeorganisasi']." - ".$key['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optdivisi.="</optgroup>";
			}
		}
		
		echo $optdivisi;
	break;

	case 'getkarid':
		if($param['jabatan']==' ' or $param['kodeorg']==' '){
			exit('Warning : Jabatan dan Lokasitugas harus dipilih !');
		}
		$optkarid="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".datakaryawan where lokasitugas='".$param['kodeorg']."' and kodejabatan='".$param['jabatan']."' order by tipekaryawan,namakaryawan asc";
		//echo $param['karyawanid'];
		$res = fetchData($str);
		foreach($res as $key){
			$optipekar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe',"id='".$key['tipekaryawan']."'");
			$d=$optipekar[$key['tipekaryawan']];
			if($d!=$n){			
				$optkarid.="<optgroup label='".$d."'>";
			}
			$s="";
			if($key['karyawanid']==$param['karyawanid']){
				$s="selected";
			}
			
			$optkarid.="<option value=".$key['karyawanid']." ".$s.">".$key['nik']." - ".$key['namakaryawan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkarid.="</optgroup>";
			}
		}
		
		echo $optkarid;
	break;
	case 'addnew':
		
		$optjabatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5jabatan where aktif='1' order by namajabatan";
		$res = fetchData($str);
		foreach($res as $val){
			$optjabatan.="<option value=".$val['kodejabatan'].">".strtoupper($val['namajabatan'])."</option>";			
		}
		
		$str = "select * from ".$dbname.".sdm_5kpi where id='".$param['id']."'";
		$res = fetchData($str);
		foreach($res as $val){
			$kpi=$val['kpi'];
			$param['kodeorg']=$val['kodeorg'];
			$param['divisi']=$val['divisi'];
		}

		$optdept="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str = "select * from ".$dbname.".sdm_5departemen where aktif='1' order by nama";
		$res = fetchData($str);
		foreach($res as $val){
			$optdept.="<option value=".$val['kode'].">".strtoupper($val['nama'])."</option>";			
		}

		$optjenis="<option value='0'>Header</option>";
		$optjenis.="<option value='1'>Subheader</option>";	
		$optjenis.="<option value='2'>Kpi Text</option>";

		$opttipepenilaian="<option value='0'>Umum</option>";
		$opttipepenilaian.="<option value='1'>Skala Penilaian</option>";		
		
		$optinduk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		

		$opttahun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$opttahun.="<option value=".(date('Y')-1).">".(date('Y')-1)."</option>";
		$opttahun.="<option value=".date('Y').">".date('Y')."</option>";			
		$opttahun.="<option value=".(date('Y')+1).">".(date('Y')+1)."</option>";			
		
		$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by induk, tipe";
		$res = fetchData($str);
		foreach($res as $key){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key['kodeorganisasi']."'");
			$d=$induk[$key['kodeorganisasi']];
			if($d!=$n){			
				$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$s="";
			if($key['kodeorganisasi']==$param['kodeorg']){
				$s="selected";
			}
			$optorg.="<option value=".$key['kodeorganisasi']." ".$s.">".$key['kodeorganisasi']." - ".$key['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optorg.="</optgroup>";
			}
		}
		
		$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 order by induk, tipe";
		$res = fetchData($str);
		foreach($res as $key){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key['kodeorganisasi']."'");
			$d=$induk[$key['kodeorganisasi']];
			if($d!=$n){			
				$optdivisi.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$s="";
			if($key['kodeorganisasi']==$param['divisi']){
				$s="selected";
			}
			$optdivisi.="<option value=".$key['kodeorganisasi']." ".$s.">".$key['kodeorganisasi']." - ".$key['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optdivisi.="</optgroup>";
			}
		}
		$place="placeholder='Formating:\nHurup tebal (bold) = <b>isi tulisan disini</b>\nHurup miring (italic) = <i>isi tulisan disini</i>\nGaris bawah (underline) = <u>isi tulisan disini</u>\n\natau gunakan = <font style=font-weight:bold;color:red;>isi tulisan disini</font>\nUntuk panduan lengkap cari di google = penulisan html atau tag html'";
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<input id=idkpi type=hidden>
					<td style=min-width:100px>".$_SESSION['lang']['tahun']."</td>
					<td colspan=5><select class='select2' style='width:500px;' id=tahun >".$opttahun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['lokasitugas']."</td>
					<td colspan=2><select class='select2' style='width:180px;' onchange=getdivisi(this.value); id=kodeorg >".$optorg."</select></td>
				
					<td hidden>".$_SESSION['lang']['divisi']."</td>
					<td hidden colspan=5><select class='select2' style='width:190px;' id=divisi >".$optdivisi."</select></td>
				</tr>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jabatan']."</td>
					<td colspan=2><select class='select2' style='width:280px;' onchange=getkarid(); id=jabatan >".$optjabatan."</select></td>
				
					<td hidden>".$_SESSION['lang']['departemen']."</td>
					<td colspan=5 hidden><select class='select2' style='width:190px;' id=dept >".$optdept."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakaryawan']."</td>
					<td colspan=2><select class='select2' style='width:180px;' id=karyawanid ></select></td>
				<tr>
					<td style=vertical-align:top>".$_SESSION['lang']['kpi']."</td>
					<td colspan=5><textarea ".$place." class=myinputtext style='width:495px;height:150px;font-size:14px;' id=kpi >".$kpi."</textarea></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['bobot']."</td>
					<td><input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=bobot ></td>
					
					<td>Target</td>
					<td><input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=target ></td>
				</tr>
				<tr>
					
					<td>Jenis</td>
					<td><select class='select2' style='width:190px;' id=jenis onchange=\"getinduk()\">".$optjenis."</select></td>

					<td>Induk</td>
					<td><select class='select2' style='width:190px;' id=induk ></select></td>
				</tr>

				<tr>
					
					<td>Tipe Penilaian</td>
					<td><select class='select2' style='width:190px;' id=tipepenilaian onchange=\"getskala()\">".$opttipepenilaian."</select></td>

					<td>Skala Penilaian</td>
					<td><select class='select2' style='width:190px;' id=skalapenilaian ></select></td>
				</tr>

                <tr>
                    <td><input type=hidden id=method value=insert>
						</td>
                    <td colspan=40>
						<button onclick=simpan(); style='width:500px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'uploaddata':
	$optpembatas="<option value='1'> , </option>";
	$optpembatas.="<option value='2'> ; </option>";
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td>INFO</td>
					<td>:</td>
					<td>Data yang diupload akan menghapus data jika sudah ada berdasarkan unit,jabatan,tahun, jadi diharapkan dalam upload data untuk unit,jabatan,tahun secara keseluruhan dan file upload hanya CSV<br>
						Untuk data jabatan bisa di download <a href=tool_slave_getExample.php?form=JABATAN target=frame>Disini</a><br>
						Untuk data karyawan bisa di download pada menu ".getMenu('sdm_2lapkaryawan','x')."<br>

					</td>
				</tr>
				<tr>
					<td>Contoh</td>
					<td>:</td>
					<td>Contoh urutan kolom dalam file upload dapat di download <a href=tool_slave_getExample.php?form=KPI target=frame>Disini</a> contoh ini menggunakan pembatas ',' </td>
				</tr>
				<tr>
					<td>Pembatas kolom/ Field seperated by </td>
					<td>:</td>
					<td><select class='select2' style='width:60px;' id=pembatas >".$optpembatas."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['file']."</td>
					<td>:</td>
					<td><input name=filex type=file id=filex size=25 class=mybutton></td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert>
						</td>
                    <td colspan=40>
						<button id='butupload' onclick=uploadfile(); style='width:500px;height:30px' class=mybutton>upload</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'uploadfile':

		$file = $_FILES['files'];
		$tempFile = $file['tmp_name'];
		if($param['pembatas']=='1'){
			$pembatas=',';
		}else{
			$pembatas=';';
		}

		if($file['type'] != 'text/csv'){
			exit('Warning: Format file tidak didukung, gunakan format file CSV ');
		}
		$optkarid = makeOption($dbname, 'datakaryawan', 'nik,karyawanid');
		if(is_uploaded_file($tempFile)){
			$openFile = fopen($tempFile,'r');
			$cekFormat = fgetcsv($openFile,0,$pembatas);
			if($cekFormat[0] != 'tahun' || $cekFormat[1] != 'unit' || $cekFormat[2] != 'jabatan' || $cekFormat[3] != 'jabatanid' || $cekFormat[4] != 'NIK' || $cekFormat[5] != 'namakaryawan' || $cekFormat[6] != 'text' || $cekFormat[7] != 'bobot' || $cekFormat[8] != 'target' || $cekFormat[9] != 'satuan'){
				exit('Warning: Format CSV tidak benar, gunakanlah format yang sudah ditentukan');
			}

			$insError = 0;
			$insErrorx = '';
			$insErrorxkar = array();
			$insSuccess = 0;
			$dataarray=array();
			while(($baris = fgetcsv($openFile,0,$pembatas)) !== FALSE) {
					$tahun = $baris[0];
				$karyawanid = $optkarid[$baris[4]];
				$strc="select * from ".$dbname.".sdm_5kpi where karyawanid='".$karyawanid."' and tahun='".$tahun."' and (status='9' or status='1')";
        		$resc=fetchData($strc);
        		if(count($resc)>0){
        			if(!isset($insErrorxkar[$karyawanid])){
        				$insErrorx.="Karyawan : ".getNamaKaryawan($karyawanid)." sudah diajukan atau disetujui , proses upload untuk karyawan ini gagal !<br>";
        				$insErrorxkar[$karyawanid]=1;
        			}
        		}else{
					$tahun = $baris[0];
					$unit = $baris[1];
					$jabatan = intval($baris[3]);
					$karyawanid = $optkarid[$baris[4]];
					$text = $baris[6];
					$bobot = $baris[7];
					$target = $baris[8];
					$satuan = $baris[9];

					$createdby=$_SESSION['standard']['userid'];
					$createdtime= date("Y-m-d H:i:s");
					$updateby= $_SESSION['standard']['userid'];

					if(!isset($dataarray[$tahun][$jabatan][$unit])){
						$querydelete = "DELETE FROM {$dbname}.sdm_5kpi WHERE tahun='{$tahun}' and jabatan='{$jabatan}' and kodeorg='{$unit}' ";
						$owlPDO->exec($querydelete);
						$dataarray[$tahun][$jabatan][$unit]=1;
					}
					## QUERY INSERT KE TABLE sdm_potonganht
					$queryInsertHt = "INSERT INTO {$dbname}.sdm_5kpi (id,tahun,jabatan,karyawanid,kodeorg,kpi,bobot,target,satuan,createdby,createdtime,updateby) VALUES ('','{$tahun}','{$jabatan}','{$karyawanid}','{$unit}','{$text}','{$bobot}','{$target}','{$satuan}','{$createdby}','{$createdtime}','{$updateby}')";

					
					try{
						//echo $queryInsertHt.';<br>';
						$owlPDO->exec($queryInsertHt);
						$insSuccess += 1;
					}catch(Exception $e){
						$insError += 1;
					}

        		}
			}
			echo "Berhasil insert {$insSuccess} data\n dan ditemukan {$insError} data duplikat <br>".$insErrorx;
		}else{
			exit('Warning: Error system');
		}

	break;
	case 'formajukan':
        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."'";
        $res=fetchData($str);

        $lokasitugas = $res[0]['lokasitugas'];


        
        ## APPROVAL DINAMIS SESUAI SETUP##
    
        //$optper=array();
        $optKryx=array();
        $optKrylevel=array();

        $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
       
                 $str="select * from ".$dbname.".setup_approval 
                where jenispersetujuan='KPIS' and kodeunit='".$lokasitugas."' and karyawaniduser=''  order by level";  
                $res=fetchData($str);
                 foreach($res as $key => $bar){
                    $whr        =" karyawanid='".$bar['karyawanid']."'";
                    $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                    
                    $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                    $optKrylevel[$bar['level']]=$bar['level'];

                }
        

        
        $jumlahlevel=count($optKrylevel);    
        $tab.="<input hidden id=notransaksi_ajukan value='".$param['karyawanid']."'>";
        $tab.="<input hidden id=notransaksi_ajukan2 value='".$param['tahun']."'>";
        if($jumlahlevel>0)
        {
                $jumlahlevel=1;
                $tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
            //foreach ($optKrylevel as $key) {
                $optKry='';
                foreach ($optKryx[1] as $key2 => $val) {
                    $optKry.=$val;
                }
                    $tab .= "<tr class=rowcontent>
                        <td>Approval ke-1</td>
                        <td width=5px>:</td>
                        <td><select id=kepada1 style='width:99%;'>".$optKry."</select></td>     
                    </tr>";
                
            //}

        }
        else
        {           $jumlahlevel=1;
                     $tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
                    $tab .= "<tr class=rowcontent>
                        <td>Approval ke-1</td>
                        <td width=5px>:</td>
                        <td><select id=kepada1 style='width:99%;'></select></td>
                    </tr>";
        }
        $tab.="<tr class=rowcontent>
                <td></td>
                <td></td>
                <td><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
            </tr>               
        </table>";
        echo $tab;
	break;
	case 'ajukan':
		
        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$param['notransaksi']."'";
        $res=fetchData($str);

        $lokasitugas = $res[0]['lokasitugas'];

		for ($i=1; $i <= $param['jlh'] ; $i++) { 
            $per['persetujuan'.$i]=checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $param['notransaksi']==''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }

        $str = "UPDATE " . $dbname . ".sdm_5kpi SET status='9' WHERE karyawanid= '" . $param['notransaksi'] . "' and tahun='".$param['tahun']."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        
        $jenispersetujuan='KPIS';
        for($i=1; $i<=$param['jlh']; $i++){
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$lokasitugas."'";
            // exit("error : $str");
            $res=fetchData($str);
            $tipeapp = $res[0]['tipe'];
            $departemenapp = $res[0]['departemen'];
            $tipekaryawanapp = $res[0]['tipekaryawan'];
            $jabatanapp = $res[0]['jabatan'];
            
            if(count($res) > 0){
                if($tipeapp=='1'){
                    if($departemenapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."/".$param['tahun']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($tipekaryawanapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."/".$param['tahun']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($jabatanapp!='0'){
                        $str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            if($per['persetujuan'.$i]!=''){
                                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."/".$param['tahun']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                                $owlPDO->exec($str);
                            }
                        }
                    }
                }else{
                    if($per['persetujuan'.$i]!=''){
                        $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."/".$param['tahun']."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
                        try
                        {
                            $owlPDO->exec($str);
                        }
                        catch (PDOException $e) 
                        {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                }
            }
        }
	break;

	case 'loaddata':
		if($param['tipe']=='excel'){
			$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='1' width=100%>
			<thead>
				<tr class=rowheader>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tahun']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jabatan']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['unit']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['departemen']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodeorg']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['divisi']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kpi']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['bobot']." (%)</th>
					<th rowspan=2 style='text-align:center;'>Target</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jenis']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['induk']."</th>
					<th rowspan=2 style='text-align:center;'>Tipe Penilaian</th>
					<th rowspan=2 style='text-align:center;'>Skala Penilaian</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
					<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updatetime']."</th>
					<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
				</tr>
				<tr class=rowheader>
					<th  style='display:none;'></th>
					<th  style='display:none;'></th>
				</tr>
			</thead>
			<tbody >";
		}

		
		$limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

		$offset    = $page * $limit;
		$maxdisplay= ($page * $limit);
		$no        = 0;
		$no        = $maxdisplay;
		$colspan   = 16;
		$where="";

		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$where.=" and kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
		}

		// $kodeOrgUser = orgDetailuser($_SESSION['standard']['username'],"2");
		// $where.=" and kodeorg IN (".$_SESSION['standard']['username'].") ";

		if($param['cari']!=''){
			$text = explode(" ",$param['cari']);
			foreach($text as $txt){
				// $nourut++;
				// if($nourut==1){					
				// }else{
					// $where.=" or ";
				// }
				
				$where.=" and ";
				$where.=" (tahun like '%".$txt."%'";
				$where.=" or kodeorg like '%".$txt."%'";
				$where.=" or divisi like '%".$txt."%'";
				$where.=" or kpi like '%".$txt."%'";
				$where.=" or jabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%".$txt."%')";
				$where.=" or dept in (select kode from ".$dbname.".sdm_5departemen where nama like '%".$txt."%')";
				$where.=" or dept in (select kode from ".$dbname.".sdm_5departemen where nama like '%".$txt."%'))";
			}
		}
		

		$sql= "select count(*) as notr from ".$dbname.".sdm_5kpi where 1=1 " . $where . "";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		
		$xxx="";
		if($param['tipe']!='excel'){
			$xxx=" limit " . $offset . "," . $limit . "";
		}

			$datainduk=array();
			$str = "select * from ".$dbname.".sdm_5kpi where jenis in ('0','1')  order by id asc";
			$res = fetchData($str);
			foreach($res as $val){
				$datainduk[$val['tahun']][$val['kodeorg']][$val['jabatan']][$val['id']]['id']=$val['id'];
				$datainduk[$val['tahun']][$val['kodeorg']][$val['jabatan']][$val['id']]['text']=$val['kpi'];
			}

			$optskala="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "select * from ".$dbname.".sdm_5setupscore where status='1' group by judul order by judul asc";
			$zres = fetchData($str);

		
		$str= "select * from ".$dbname.".sdm_5kpi where 1=1 " . $where . " order by jabatan asc, id desc ".$xxx."";
		$res= fetchdata($str);
		foreach($res as $bar){
			/* $e=$bar['tahun'];
			if($e!=$o){
				$no+=1;
				$tab.="<tr class=rowcontent style=background-color:#e8e8e8>";
				$tab.="<td style='text-align:center;'>".$no."</td>";
				$tab.="<td style='text-align:left;'>".$e."</td>";
				$tab.="<td style='text-align:left;'></td>";
				$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
				$tab.="</tr>";
			}
			
			$d=$bar['jabatan'];
			if($d!=$n){
				$no+=1;
				$tab.="<tr class=rowcontent style=background-color:#e8e8e8>";
				$tab.="<td style='text-align:center;'>".$no."</td>";
				$tab.="<td style='text-align:left;'></td>";
				$tab.="<td style='text-align:left;'>".getNamaJabatan($d)."</td>";
				$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
				$tab.="</tr>";
			} */
			
			if($bar['dept']!=""){
				$dept=getNamaDept($bar['dept']);
			}else{
				$dept=$_SESSION['lang']['all'];
			}
			if($bar['kodeorg']!=""){
				$kodeorg=getNamaOrg($bar['kodeorg']);
			}else{
				$kodeorg=$_SESSION['lang']['all'];
			}
			if($bar['divisi']!=""){
				$divisi=getNamaOrg($bar['divisi']);
			}else{
				$divisi=$_SESSION['lang']['all'];
			}
			
			$optinduk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			foreach ($datainduk[$bar['tahun']][$bar['kodeorg']][$bar['jabatan']] as $val) {
				if($val['id']==$bar['induk']){
					$optinduk.="<option selected value='".$val['id']."'>".$val['text']."</option>";
				}else{
					$optinduk.="<option value='".$val['id']."'>".$val['text']."</option>";
				}
			}

			foreach($zres as $zval){
				if($bar['penilaian']==$zval['judul']){
					$optskala.="<option selected value=".$zval['judul'].">".$zval['judul']."</option>";
				}else{
					$optskala.="<option value=".$zval['judul'].">".$zval['judul']."</option>";
				}
			}

			if($bar['induk']==''){
				$optinduk='';
			}

			if($bar['penilaian']==''){
				$optskala='';
			}
			

			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$bar['tahun']."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
			$tab.="<td style='text-align:left;'>".getNamaJabatan($bar['jabatan'])."</td>";
			$tab.="<td style='text-align:left;'>".getNik($bar['karyawanid'])."-".getNamaKaryawan($bar['karyawanid'])."</td>";
			$tab.="<td style='text-align:left;'>".nl2br($bar['kpi'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['bobot']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['target']."</td>";
			$tab.="<td style='text-align:left;'>".$arrjenis[$bar['jenis']]."</td>";
			$tab.="<td style='text-align:left;'>".$arrtext[$bar['induk']]."</td>";
			$tab.="<td style='text-align:left;'>".$arrtipepenilaian[$bar['tipepenilaian']]."</td>";
			$tab.="<td style='text-align:left;'>".$bar['penilaian']."</td>";
			$tab.="<td style='text-align:left;'>".getKary($bar['updateby'])."</td>";
			$tab.="<td style='text-align:left;'>".$bar['lastupdate']."<select id=optindukx".$bar['id']." hidden>".$optinduk."</select><select id=optskalax".$bar['id']." hidden>".$optskala."</select></td>";
			if($param['tipe']!='excel'){
				$tab.="<td style='text-align:center;width:25px'>
					<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['tahun']."','".$bar['jabatan']."','".$bar['dept']."','','".$bar['bobot']."','".$bar['target']."','".$bar['id']."','".$bar['kodeorg']."','".$_SESSION['empl']['tipelokasitugas']."','".$_SESSION['empl']['bagian']."','".$bar['karyawanid']."','".$bar['jenis']."','".$bar['tipepenilaian']."')\";></td>";
				$tab.="<td style='text-align:center;width:25px'>
					<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');></td>";
			}elseif($param['tipe']!='excel'){
				$tab.="<td style='text-align:center;width:25px' colspan='2'></td>";
			}
			$tab.="</tr>";

			$n=$d;
			$o=$e;
		}
		
		
		if($param['tipe']=='excel'){
			$tab.="</tbody>
			<tfoot>
			</tfoot>
			</table>";
			$nop = "kpi.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("kpi", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			$foot=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata2','getPage');
			echo $tab."####".$foot;
		}
		
	break;
}
?>

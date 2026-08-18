<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$path   = "fileupload/efiling_legal/";
$arrEnumTipeSurat=getEnum($dbname,'lgl_efiling','tipesurat');

$namanjenisurat=makeOption($dbname,'lgl_5jenissurat_efiling','id,jenis');



switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['nosurat']."</th>
				<th align=center>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['departemen']."</th>
				<th align=center>Jenis ".$_SESSION['lang']['surat']."</th>
				<th align=center>".$_SESSION['lang']['tanggalsurat']."</th>
				<th align=center>".$_SESSION['lang']['createby']."</th>
				<th align=center>".$_SESSION['lang']['createtime']."</th>
				<th align=center>Posting By</th>
				<th align=center>Posting Time</th>
				<th align=center >".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		$no=0;
		$str= "select * from ".$dbname.".lgl_efiling";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$val['nosurat']."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($val['kodept'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['departemen']."</td>";
			$tab.="<td style='text-align:center;'>".$namanjenisurat[$val['jenissurat']]."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['tanggalsurat'])."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['createby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['createtime'])."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['postingby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['postingtime'])."</td>";
			$tab.="<td style='text-align:center;'>";
			if($val['posting'] == 0 ){
				$tab.="<img  style=margin-right: 5px; src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."','".$val['nosurat']."','".$val['kodept']."','".$val['departemen']."','".$val['jenissurat']."','".tanggalnormald($val['tanggalsurat'])."','".$val['dari']."','".$val['jabatan']."','".$val['untuk']."','".$val['keterangan']."','".$val['masalapor']."','".$val['reminder']."','".tanggalnormald($val['tanggallapor'])."','".$val['tipesurat']."')\";>
						<img  style=margin-right: 5px; src='images/application/application_delete.png' class='resicon' title='Delete' onclick=\"deletedata('".$val['id']."','".$val['kodept']."','".$val['nosurat']."','".$val['jenissurat']."')\";>
						<img src='images/red/posting.png' class='resicon'  title='Posting' onclick=\"posting('".$val['id']."','".$val['nosurat']."')\";>";
						
			}else{
				$tab.="<img src='images/icons/04/16/02.png' class='resicon'  title='Posted'>
				<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$val['id']."','".$val['nosurat']."','".$val['kodept']."','".$val['jenissurat']."')\" src='images/upload-2-xxl.png'/>";
			}	
			
			$tab.="</td>";
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
	case 'showupload':
		$tab="";
		$tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td>
					<label id='ptupload' style='display:none'>".$param['kodept']."</label>
					<label style='font-weight:bold'>".$param['kodept']."</label>
				</td>
			</tr>
			<tr hidden>
				<td>Tipe ".$_SESSION['lang']['surat']."</td>
				<td>:</td>
				<td>
					<label id='xxx' style='font-weight:bold'>".$param['tipesurat']."</label>
				</td>
			</tr>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['nosurat']."</td>
				<td>:</td>
				<td>
					<label id='iii' style='font-weight:bold'>".$param['nosurat']."</label>
				</td>
			</tr>";			
		$tab.="<tr><td colspan=4><hr></td></tr>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload' >
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"submitfile()\">Submit</button>
					</td>
				</tr>
			</table>
			<p />";
			
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
			
		echo $tab;
	break;

	case 'submitfile':
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $pt."_".$his."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					// if($_FILES['file']['size'] <= 1000000){
						$str = "insert into ".$dbname.".listfile_lgl_efiling values ('','".$param['kodept']."','".$param['tipesurat']."','".$param['nosurat']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."',0,'','')";
						try{
							$owlPDO->exec($str);
							if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
							file_put_contents($path.$filename,$file_tmpname);
						}
						catch(PDOException $e){
							echo " Gagal," . addslashes($e->getMessage());
						}
					// }else{
					// 	exit("warning : Ukuran file upload maksimal 250kb");
					// }
				}else{
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
			}
		}
	break;

	case 'loadfiles':
		$no = 0;
		$tab = "";	
		$str="select * from ".$dbname.".listfile_lgl_efiling where kodept = '".$param['kodept']."' and status='1' and tipesurat='".$param['tipesurat']."' and nosurat='".$param['nosurat']."' ";
		//exit('error'.$str);
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}elseif($val['formaticon']=='.png'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}elseif($val['formaticon']=='.pdf'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}else{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}

				if($val['posting'] == '0'){
					$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['kodept']."','".$val['nosurat']."','".$val['tipesurat']."','".$val['namafile']."');\">
					<img src='images/red/posting.png' class='resicon'  title='Posting' onclick=\"postingFile('".$val['kodept']."','".$val['nosurat']."','".$val['tipesurat']."','".$val['namafile']."')\";>";
					

				}else{
					$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
					
				}
				
				
				
				$tab."	</td>
				</tr>";
			}	
		}
		
		echo $tab;
	break;

	case 'deletefile':
		
		$str="delete from ".$dbname.".listfile_lgl_efiling where kodept='".$param['kodept']."' and nosurat='".$param['nosurat']."' and tipesurat='".$param['tipesurat']."' and namafile='".$param['namafile']."'"; //exit('error'.$str);
		try{
			$owlPDO->exec($str);
			$pathx = $path.$namafile;
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	case'viewfile':
		$tab="";
		$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
		
		echo $tab;
	break;

	case 'gettanggallapor':
		$nextMonthDate = date('Y-m-d', strtotime('+ '.$param['masalapor'].' month', strtotime(tanggalsystemn($param['tanggalsurat']))));		
		echo tanggalnormal($nextMonthDate);
	break;
	
	case 'addnew':		

        ## GET Kode PT
        $optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }

        ## GET Kode Departemen
        $optdepartemen = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str="select * from ".$dbname.".lgl_5departemen_efiling where 1=1  order by departemen asc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optdepartemen.="<option value=".$bar['departemen'].">".$bar['departemen']."</option>";
        }

        ## GET Jenis Surat
        $optjenissurat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str="select * from ".$dbname.".lgl_5jenissurat_efiling where 1=1 order by jenis asc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optjenissurat.="<option value=".$bar['id'].">".$bar['jenis']."</option>";
        }

        ## GET Kode Jabatan
        $optjabatan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str="select * from ".$dbname.".lgl_5jabatan_efiling where 1=1 order by jabatan asc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optjabatan.="<option value=".$bar['jabatan'].">".getNamaJabatan($bar['jabatan'])."</option>";
        }

        ## GET Tipe Surat
        $optipesurat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach($arrEnumTipeSurat as $val){
			$optipesurat .= "<option value='".$val."'>".$val."</option>";
		}


        $tab="";
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
			 <input disabled hidden class=myinputtext id='idx' style=width:247px>
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>
					<td>
						<select style='width:250px;' class='select2' id='kodept' >".$optorg."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['departemen']."</td>
					<td>:</td>
					<td>
                        <select style='width:250px;' class='select2' id='departemen' >".$optdepartemen."</select>
					</td>
				</tr>
                <tr>
					<td>Jenis ".$_SESSION['lang']['surat']."</td>
					<td>:</td>
					<td>
                        <select style='width:250px;' class='select2' id='jenissurat' >".$optjenissurat."</select>
					</td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['tanggalsurat']."</td>
					<td>:</td>
					<td>
                        <input id='tanggalsurat' type='text' style='width:247px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
					</td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['dari']."</td>
					<td>:</td>
					<td>
					    <input class=myinputtext id='dari' style=width:247px>
					</td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['jabatan']."</td>
					<td>:</td>
					<td>
                        <select style='width:250px;' class='select2' id='jabatan' >".$optjabatan."</select>
					</td>
				</tr>
                <tr>
					<td>Untuk</td>
					<td>:</td>
					<td>
					    <input class=myinputtext id='untuk' style=width:247px>
					</td>
				</tr>
                <tr>
					<td>Perihal & ".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td>
					    <textarea rows=3 maxlength=124 id='keterangan' type=text onkeypress='return tanpa_kutip(event)' style='width:550px;'></textarea>
					</td>
				</tr>
                <tr hidden>
                    <td colspan=3><hr></td>
                </tr >
                <tr hidden>
                    <td>Pelaporan Progress Surat</td>
                    <td>:</td>
                    <td>
                        Masa Lapor Awal (Bln) : <input type=number oninput=gettanggallapor(); class=myinputtext id=masalapor style=width:50px> 
                        Tanggal Lapor : <input disabled id='tanggallapor' type='text' style='width:100px;' class='myinputtext'/>
					</td>
                </tr>
                <tr hidden>
					<td colspan=2></td>
					<td>
						Reminder (Bln): <input type=number class=myinputtext id=reminder style=width:50px>  Sebelum tgl lapor
					</td>
				</tr>
                <tr hidden>
                    <td colspan=3><hr></td>
                </tr>
                <tr>
                    <td>Tipe Surat</td>
                    <td>:</td>
                    <td>
                        <select style='width:250px;' class='select2' id='tipesurat' >".$optipesurat."</select>
                    </td>
                </tr>
                 <tr>
					<td>".$_SESSION['lang']['nosurat']."</td>
					<td>:</td>
					<td>
					    <input class=myinputtext id=nosurat style=width:247px>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td colspan=4>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
                    </td>
                </tr>
            </table>";
		
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(nosurat) as jlhitem from ".$dbname.".lgl_efiling where nosurat='".$param['nosurat']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("No Surat sudah ada!  ");
			}
			
			$data = array(
				'kodept'		=> $param['kodept'],
				'departemen'	=> $param['departemen'],
				'jenissurat'	=> $param['jenissurat'],
				'tanggalsurat'	=> tanggaldb($param['tanggalsurat']),
				'dari'			=> $param['dari'],
				'jabatan'		=> $param['jabatan'],
				'untuk'			=> $param['untuk'],
				'keterangan'	=> $param['keterangan'],
				'masalapor'		=> $param['masalapor'],
				'reminder'		=> $param['reminder'],
				'tanggallapor'	=> tanggaldb($param['tanggallapor']),
				'tipesurat'		=> $param['tipesurat'],
				'nosurat'		=> $param['nosurat'],
				'createby'		=> $_SESSION['standard']['userid'] ,
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'lgl_efiling',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'kodept'		=> $param['kodept'],
				'departemen'	=> $param['departemen'],
				'jenissurat'	=> $param['jenissurat'],
				'tanggalsurat'	=> tanggaldb($param['tanggalsurat']),
				'dari'			=> $param['dari'],
				'jabatan'		=> $param['jabatan'],
				'untuk'			=> $param['untuk'],
				'keterangan'	=> $param['keterangan'],
				'masalapor'		=> $param['masalapor'],
				'reminder'		=> $param['reminder'],
				'tanggallapor'	=> tanggaldb($param['tanggallapor']),
				'tipesurat'		=> $param['tipesurat'],
				'nosurat'		=> $param['nosurat'],
				'updateby'		=> $_SESSION['standard']['userid'] ,
				'updatetime'	=> date('Y-m-d H:i:s')
			);

			$where = " id ='".$param['idx']."'";
			$str = updateQuery($dbname,'lgl_efiling',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'deletedata':
		try {
			$owlPDO->beginTransaction();
			
			$where = " id ='".$param['idx']."'";
			$str = deleteQuery($dbname,'lgl_efiling',$where);
			$owlPDO->exec($str);


			$str = deleteQuery($dbname,'listfile_lgl_efiling',"kodept='".$param['kodept']."' and nosurat='".$param['nosurat']."' and tipesurat='".$param['tipesurat']."'");
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case'posting':
		$data = array(
			'posting' => 1,
			'postingby' => $_SESSION['standard']['userid'],
			'postingtime' => date('Y-m-d H:i:s')
		);
		
		$where = " id ='".$param['idx']."' and nosurat = '".$param['nosurat']."' ";
		$str = updateQuery($dbname,'lgl_efiling',$data,$where);
		$owlPDO->exec($str);
	break;
	case'postingFile':
		$data = array(
			'posting' => 1,
			'postingby' => $_SESSION['standard']['userid'],
			'postingtime' => date('Y-m-d H:i:s')
		);
		
		$where = " kodept ='".$param['kodept']."' and nosurat = '".$param['nosurat']."'  and tipesurat = '".$param['tipesurat']."' and namafile = '".$param['namafile']."' ";
		$str = updateQuery($dbname,'listfile_lgl_efiling',$data,$where);
		$owlPDO->exec($str);
	break;
}
?>

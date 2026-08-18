<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$pages = checkPostGet('page','');
$scmodul = checkPostGet('scmodul','');
$scjudul = checkPostGet('scjudul','');
$createtime=date("Y-m-d H:i:s");
$arrstatus = array('A'=>'Aktif','D'=>'Non-Aktif');

switch ($method){
	case'loaddata':
		$tab="";
        $limit = 20;
        $page = 0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=@($page*$limit);
		$no=@(($page*$limit));
		$colspan=8;
		
		$where="";
		if($scmodul!=''){
			$where.=" and modul='".$scmodul."'";
		}
		if($scjudul!=''){
			$where.=" and judul like '%".$scjudul."%'";
		}
		

        $str = "select id from ".$dbname.".owlhelp where 1=1 ".$where." group by modul, judul";
		$res=fetchdata($str);
		$jlhbrs = count($res);
				
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no = 0;
			$optbahasa=makeOption($dbname,'namabahasa','code,name');
			$str = "select * from ".$dbname.".owlhelp where 1=1 ".$where." group by modul, judul order by modul asc, judul asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				$optmodul=makeOption($dbname,'menu','id,caption',"id='".$val['modul']."'");
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$optmodul[$val['modul']]."</td>";
				$tab.="<td>".$val['judul']."</td>";
				
				$tab.="<td>";
				$strx="select * from ".$dbname.".owlhelp where modul='".$val['modul']."' and judul='".$val['judul']."' order by bahasa asc";
				$resx=fetchdata($strx);
				$nox=0;
				foreach($resx as $valx){
					$nox++;
					if($nox==1){
						// $tab.="<a href='help/upload/".$valx['namafile']."' download>".$optbahasa[$valx['bahasa']]."</a>";
						$tab.="<label onclick=\"viewpdf('".$valx['modul']."','".$optbahasa[$val['modul']]."','".$valx['judul']."','".$valx['bahasa']."',event)\" style='color:blue;cursor:pointer' title='Klik untuk menampilkan PDF'>".$optbahasa[$valx['bahasa']]."</label>";
					}else{
						// $tab.="<br><a href='help/upload/".$valx['namafile']."' download>".$optbahasa[$valx['bahasa']]."</a>";
						$tab.="<br><label onclick=\"viewpdf('".$valx['modul']."','".$optbahasa[$val['modul']]."','".$valx['judul']."','".$valx['bahasa']."',event)\" style='color:blue;cursor:pointer' title='Klik untuk menampilkan PDF'>".$optbahasa[$valx['bahasa']]."</label>";
					}
				}
				$tab.="</td>";
				$tab.="<td align='center'>".$arrstatus[$val['status']]."</td>";
				$tab.="<td align='center'>".getNamaKaryawan($val['updateby'])."</td>";
				
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$val['modul']."','".$val['judul']."');\">
				</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$val['modul']."','".$val['judul']."');\">&nbsp;
				</td>";
				
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
	break;
		
	case'batal':
		$_SESSION['helpupload']=array();
	break;
	
	case'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['judul']==''){
			exit("Gagal, Judul harus diisi.");
		}
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $data['modul']."_".$data['judul']."_".$data['bahasa']."".$filetype;
				$filename=str_replace(' ','',$filename);
				$file_tmpname = $_FILES['file']['tmp_name'];

				if($filetype=='.pdf'){
					$newdata = array(
						'bahasa'=>$data['bahasa'],
						'namafile'=>$filename,
						'filetype'=>$filetype
					);
					
					if($_SESSION['helpupload'] != array()){
						foreach($_SESSION['helpupload'] as $key=>$row){
							if($row['bahasa'] == $data['bahasa']){
								exit("Warning : Item ini sudah pernah diinput sebelumnya.");
							}
						}
						array_push($_SESSION['helpupload'],$newdata);
					}else{
						array_push($_SESSION['helpupload'],$newdata);
					}
					move_uploaded_file($file_tmpname,"help/upload/$filename");
				}else{
					exit("Warning : Format file upload harus .pdf");
				}
			}
		}
	break;
	
	case'loadfiles':
		$tab="";
		$no=0;
		if(count($_SESSION['helpupload']) > 0){
			foreach($_SESSION['helpupload'] as $key=>$val){
				$no++;
				$optbhs=makeOption($dbname,'namabahasa','code,name',"code='".$val['bahasa']."'");
				$tab.="<tr class='rowcontent'>";
				$tab.="<td>".$optbhs[$val['bahasa']]."</td>";
				$tab.="<td title='Klik disini untuk download file'><a href='help/upload/".$val['namafile']."' download>".$val['namafile']."</a></td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletefile('".$val['namafile']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center' colspan=3>".$_SESSION['lang']['datanotfound']."</td>";
			$tab.="</tr>";
		}
        echo $tab;
	break;
	
	case'deletefile':
		$namafile = checkPostGet('namafile','');
		foreach($_SESSION['helpupload'] as $key=>$val){
			if($val['namafile'] == $namafile){
				// $path = "help/upload/".$namafile;
				// unlink($path);
				unset($_SESSION['helpupload'][$key]);
			}
		}
	break;
	
	case'insert':
		$modul = checkPostGet('modul','');
		$judul = checkPostGet('judul','');
		
		if(count($_SESSION['helpupload']) <= 0){
			exit("Gagal, File upload masih kosong.");
		}
		
		$str="select id from ".$dbname.".owlhelp where modul='".$modul."' and judul='".$judul."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			exit("Gagal, Modul dan Judul sudah pernah diinput sebelumnya.");
		}
		
		foreach($_SESSION['helpupload'] as $key=>$val){
			$str="insert into ".$dbname.".owlhelp(modul,bahasa,judul,namafile,formaticon,createdby,createdtime,updateby,updatetime) values('".$modul."','".$val['bahasa']."','".$judul."','".$val['namafile']."','".$val['filetype']."','".$_SESSION['standard']['userid']."','".$createtime."','".$_SESSION['standard']['userid']."','".$createtime."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
	break;
	
	case'update':
		$modul = checkPostGet('modul','');
		$judul = checkPostGet('judul','');
		$modulold = checkPostGet('modulold','');
		$judulold = checkPostGet('judulold','');
		
		if(count($_SESSION['helpupload']) <= 0){
			exit("Gagal, File upload masih kosong.");
		}
		
		$str="delete from ".$dbname.".owlhelp where modul='".$modulold."' and judul='".$judulold."'";
		$owlPDO->exec($str);
		
		foreach($_SESSION['helpupload'] as $key=>$val){
			$str="insert into ".$dbname.".owlhelp(modul,bahasa,judul,namafile,formaticon,createdby,createdtime,updateby,updatetime) values('".$modul."','".$val['bahasa']."','".$judul."','".$val['namafile']."','".$val['filetype']."','".$_SESSION['standard']['userid']."','".$createtime."','".$_SESSION['standard']['userid']."','".$createtime."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
	break;
	
	case'deldata':
		$modul = checkPostGet('modul','');
		$judul = checkPostGet('judul','');
		
		$str="select * from ".$dbname.".owlhelp where modul='".$modul."' and judul='".$judul."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$path = "help/upload/".$val['namafile'];
			@unlink($path);
		}
		
		$str="delete from ".$dbname.".owlhelp where modul='".$modul."' and judul='".$judul."'";
		$owlPDO->exec($str);
	break;
	
	case'showData':
		$modul = checkPostGet('modul','');
		$judul = checkPostGet('judul','');
		
		$str="select * from ".$dbname.".owlhelp where modul='".$modul."' and judul='".$judul."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$newdata = array(
				'bahasa'=>$val['bahasa'],
				'namafile'=>$val['namafile'],
				'filetype'=>$val['formaticon']
			);
			array_push($_SESSION['helpupload'],$newdata);
		}
	break;
	
	case'viewpdf':
		echo "<embed src='help/upload/Pengadaan_cara membuat pr_id.pdf' width='800px' height='450px' />";
	break;
}
?>
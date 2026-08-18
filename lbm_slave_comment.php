<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/formReport.php');
include_once('lib/zFunction.php');


$method = checkPostGet('method', '');

$fileupload = checkPostGet('fileupload', '');
$file = checkPostGet('file', '');
$unit = checkPostGet('unit', '');
$per = checkPostGet('per', '');
$afd = checkPostGet('afd', '');
$val=checkPostGet('val', '');
$tipe=checkPostGet('tipe', '');
$text=checkPostGet('text', '');
$doc=checkPostGet('doc', '');
$file=checkPostGet('file', '');
$no=checkPostGet('no', '');
$isi=checkPostGet('isi', '');


$postJabatan = getPostingJabatan('lbm');	


$fileupup = checkPostGet('fileupup', '');



$dir='fileupload/lbm/';
$path = $dir;

switch($method){
	
	
	case'savefile':
		//exit("Error:$fileupload._.$file._.$unit._.$per._.$afd._.$val");
		if($_SESSION['empl']['lokasitugas']!=$unit){
			echo "Anda tidak bisa menyimpan file selain unit anda";
		}else{
			
			$data = $_POST;
			if($data['fileupload']!=''){
				if($_FILES['fileup']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
					$filename = $_FILES['fileup']['name'];
					$file_tmpname = file_get_contents($_FILES['fileup']['tmp_name']);
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						$filepath = $path.$filename;
						$str="delete from ".$dbname.".`lbm_file` where file='".$file."' and unit='".$unit."' and divisi='".$afd."' and periode='".$per."' and parameter='".$val."' and path='".$filepath."'";
						try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

						$str="INSERT INTO ".$dbname.".`lbm_file` (`file`, `unit`, `divisi`, `periode`, `parameter`,`path`, `updateby`) VALUES ('".$file."', '".$unit."', '".$afd."', '".$per."', '".$val."','".$filepath."', '".$_SESSION['standard']['userid']."')";
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
						echo $_SESSION['lang']['datatersimpan'];
					}else{
						exit("Warning : Format file upload harus .xls, .doc, .pdf, .jpg atau .jpeg");
					}
				}
			}			
		}
	break;
	
	
	
	case'deletefile':
		if($_SESSION['empl']['lokasitugas']!=$unit){
			echo "Anda tidak bisa menghapus selain unit anda";
		}else{
			$str=" delete from ".$dbname.".lbm_file where file='".$file."' and unit='".$unit."' and divisi='".$afd."' and periode='".$per."'
					and parameter='".$val."' and path='".$doc."' ";
			try{$owlPDO->exec($str); 
				$pathx = $doc;
				unlink($pathx);
			
			}
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			echo $_SESSION['lang']['deleted'];
		}
	
	break;
	
	case'savecomment':
		#delete 1st
		
		if($_SESSION['empl']['lokasitugas']!=$unit){
			echo "Anda tidak bisa memberikan komentar selain unit anda";
		}else{
			$str=" delete from ".$dbname.".`lbm_comment` where file='".$file."' and unit='".$unit."' and periode='".$per."' and divisi='".$afd."' and parameter='".$val."' ";
			try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
		
			$str="INSERT INTO ".$dbname.".`lbm_comment` (`file`, `unit`, `divisi`, `periode`, `parameter`,
					`text`, `updateby`) 
					VALUES ('".$file."', '".$unit."', '".$afd."', '".$per."', '".$val."',
					'".$text."', '".$_SESSION['standard']['userid']."')";
			try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			echo $_SESSION['lang']['datatersimpan'];
		}
	break;
	
	
	
	case'deletecomment':
		if($_SESSION['empl']['lokasitugas']!=$unit)
		{
			echo "Anda tidak bisa menghapus komentar selain unit anda";
		}
		else
		{
			$str=" delete from ".$dbname.".lbm_comment where file='".$file."' and unit='".$unit."' and divisi='".$afd."' and periode='".$per."'
					and parameter='".$val."'";
			try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			echo $_SESSION['lang']['deleted'];
		}
	break;
	
	
	
	case'isifile':
	
	
	$potong=explode('.',$doc);
	
	if($potong[1]=='pdf')
	{
		echo"<embed src=".$doc." width=780px height=370px>";
	}
	else
	{
		echo"<img src=".$doc.">";
	}
	//echo $doc;
	
	 /*$path[$bar['divisi']][$bar['periode']].="<a href=".$bar['path'].">".$bar['path']."</a>
			<img src=images/application/application_delete.png class=resicon title='Delete' 
                    onclick=\"parent.deletefile('".$file."','".$unit."','".$bar['periode']."','".$bar['divisi']."','".$val."','".$bar['path']."','html','event');\">
			<br>";*/
	break;
	
	case'detailcomment':
		echo" Print Excel : <img style=cursor:pointer; "
		. " onclick=\"parent.detailcomment('".$file."','".$unit."','".$per."','".$afd."','".$val."','excel',event)\" src=images/excel.jpg  
			title='MS.Excel'>
		   ";
		if($tipe=='excel')
		{
			$border="border=1";
		}
		else
		{
              $theme=$_SESSION['theme'];
              if($theme=='skyblue' || $theme==''){
                $gen='generic.css';
              }else if($theme=='red'){
                $gen='genericRed.css';  
              }else{
                $gen='genericGray.css';  
              }  			
			$border="border=0";
			$stream.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		}
		$stream.="<fieldset style=height:92%><legend><b>".$_SESSION['lang']['penjelasan']."</b></legend >
					<table cellpading=1 cellspacing=1 ".$border." class=sortable >
					<thead>
						<tr class=rowheader>
							<td align=center width=30px>".$_SESSION['lang']['nourut']."</td> 
							<td align=center width=50px>".$_SESSION['lang']['divisi']."</td>  							
							<td align=center width=55px>".$_SESSION['lang']['periode']."</td>  
							<td align=center width=420px colspan=2>".$_SESSION['lang']['penjelasan']."</td>
							<td align=center width=190px colspan=2>File</td>
						</tr>
					</thead>";
					
		$afdsort='';			
		if($afd!='')
		{
			$afdsort=" and divisi='".$afd."' ";
		}		
		
		
		#cek periode akuntansi aktif
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and tutupbuku=1 order by periode desc limit 1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$peraktif=$bar['periode'];
			
			
		
		$str=" select * from ".$dbname.".lbm_comment where file='".$file."' and unit='".$unit."' and parameter='".$val."' ".$afdsort."   ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	{	
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$period[$bar['periode']]=$bar['periode'];
			$listperiod[$bar['divisi']][$bar['periode']]=$bar['periode'];
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) && $method!='excel'  && $bar['periode']>=$peraktif){
				$deltext[$bar['divisi']][$bar['periode']]="<img src=images/application/application_delete.png style=width:10px;cursor:pointer; title='Delete' 
                    onclick=\"parent.deletecomment('".$file."','".$unit."','".$bar['periode']."','".$bar['divisi']."','".$val."','html','event');\">";
				$textlist[$bar['divisi']][$bar['periode']]=$bar['text'];
			}else{
				$textlist[$bar['divisi']][$bar['periode']]=$bar['text'];
			}
			
		}
		
		$str=" select * from ".$dbname.".lbm_file where file='".$file."' and unit='".$unit."' and parameter='".$val."' ".$afdsort."  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	{	
			$pathlist=$bar['path'];
			$pathlist=explode('/',$pathlist);
			$rowfile=count($pathlist);
			$filelist=$pathlist[($rowfile-1)];
			
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$period[$bar['periode']]=$bar['periode'];
			$listperiod[$bar['divisi']][$bar['periode']]=$bar['periode'];
		
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) && $method!='excel'  && $bar['periode']>=$peraktif){
				$pathx[$bar['divisi']][$bar['periode']].="<a style=cursor:pointer; onclick=\"parent.isifile('".$bar['path']."','event');\">".$filelist."</a>&nbsp;&nbsp;
					<img src=images/application/application_delete.png style=width:10px;cursor:pointer; title='Delete' onclick=\"parent.deletefile('".$file."','".$unit."','".$bar['periode']."','".$bar['divisi']."','".$val."','".$bar['path']."','html','event');\"><br>";
				
				
			}else{
				$pathx[$bar['divisi']][$bar['periode']].="<a style=cursor:pointer; onclick=\"parent.isifile('".$bar['path']."','event');\">".$filelist."</a><br>";
			}
			
		}
		
		if(!empty($kddivisi)){
			array_multisort($kddivisi,SORT_ASC);
			array_multisort($period,SORT_ASC);
				foreach($kddivisi as $divisi){
					foreach($period as $perio){
						if($listperiod[$divisi][$perio]!=''){
							$no+=1;
							$stream.="
								<tr class=rowcontent>
								<td align=center valign=top >".$no."</td>
									<td align=center valign=top >".$divisi."</td>
									<td align=center valign=top >".$perio."</td>
									<td align=left valign=top >".$textlist[$divisi][$perio]."</td>
									<td align=center valign=top width=30px>".$deltext[$divisi][$perio]."</td>
									<td valign=top ><u><font color=blue>".@$pathx[$divisi][$perio]."</foot></u></td>
								</tr>
							";
						}
					}
				}
		}
		
		
		$stream.="</table>";
		
		
		
	
		//$stream.="<embed src=fileupload/lbm/block_64.png width=400px height=400px>";
	
	if($tipe=='excel')
	{
		$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
		$nop_="detail_comment_".$unit;
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
				exit;
			}
			else 
			{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
			}
			fclose($handle);
		}
		
	}
	else
	{
	   echo $stream;
		} 
	
	break;
	case'showpopup':
		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){
		  $gen='generic.css';
		}else if($theme=='red'){
		  $gen='genericRed.css';  
		}else{
		  $gen='genericGray.css';  
		} 
		echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>";
		//echo "<pre>";
		
		$tab="";
		$tab.="<fieldset><legend>Penjelasan</legend>";
		$tab.="<table width=650px>";
		$tab.="<tr>";
		$tab.="<td>Penjelasan</td>";
		$tab.="</tr><tr>";
		$tab.="<td colspan=3><textarea rows='7' maxlength='1024' id=textx".$no."  type='text' style='width:650px;'>".$isi."</textarea></td>";
		$tab.="</tr><tr>";
		$tab.="<td><button class=mybutton onclick=\"savecomment('".$file."','".$unit."','".$per."','".$afd."','".$val."','".$no."')\">Save</button></td>";
		$tab.="</tr>";
		
		$tab.="</table>";
		$tab.="</fieldset>";
		
		$tab.="<fieldset><legend>Upload</legend>
			<table border=0 >
			<tr>
				<td>Filename</td>
				<td>
					<input type='file' name='fileupload' id='fileuploadx".$no."' >
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"savefile('".$file."','".$unit."','".$per."','".$afd."','".$val."','".$no."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";
			

		echo $tab;
	break;
	
	
	
	
}

?>
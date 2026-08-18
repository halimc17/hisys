<?php
//author : Atwal
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/paging.php');

$jabatan = checkPostGet('jabatan', '');

if(isset($_GET['switch'])){
	$switch=$_GET['switch'];
}else{
	$switch = "";
}

$err = "";
switch ($switch){
	case 'insert':
		$str = "";
		if(isset($_POST)){
			$data =$_POST;
		}else{
			$data = "";
		}
		if($data != ""){
			if($_POST['subject'] != "" and $_POST['isi'] != "" ){
				$assgn=array();
				if(isset($_POST['assignmentto'])){
					$assgn = $_POST['assignmentto'];
				}
					
					$jabatanto 	=  $_POST['jabatanto'];
					$departementto 	=  $_POST['departementto'];
					$assignmentto=array();
					foreach($assgn as $v){
						$assignmentto[] 	=  str_pad($v, 10, "0", STR_PAD_BOTH);
					}
					$subject 		= $_POST['subject'];
					$isi 			= $_POST['isi'];
					$startdate		= date("Y-m-d",strtotime($_POST['startdate']));
					$targetdate		= date("Y-m-d",strtotime($_POST['targetdate']));
					$notes			= $_POST['note'];
					$datenow		= date('Y-m-d');
				$strdoc ="INSERT INTO ".$dbname.".`sdm_taskdocument` 
							(`subject`, `description`, `notes`) values ('".$subject."','".$isi."','".$notes."'); ";
				if($_POST['jabatanto'] !== "" ){
					if(count($assignmentto) == 0){
						$sqljbtn = "select datakaryawan.karyawanid, datakaryawan.namakaryawan
						from " . $dbname . ".datakaryawan 
						left join ".$dbname.".sdm_5tipekaryawan on sdm_5tipekaryawan.id = datakaryawan.tipekaryawan
						where datakaryawan.kodejabatan = '".$jabatanto."' 
						and sdm_5tipekaryawan.no <= 5";
						$res=$owlPDO->query($sqljbtn) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while($bar=$res->fetch())
						{
							$assignmentto[$bar['karyawanid']]=$bar['karyawanid'];
						}
					}
					if(count($assignmentto) == 0){
						exit('ERROR:Karyawan dengan jabatan ini tidak ada');
					}
				}

				
				if($strdoc!=""){
					try{	
						$owlPDO->exec($strdoc);
						$id = $owlPDO->lastinsertId();

						$jmlid=count($assignmentto);
						$str="INSERT INTO ".$dbname.".`sdm_taskassignment` 
								(`iddoc`, `departementto`, `jabatanto`, `assignedto`, `startdate`,`targetdate`,`status`,`createby`,`createdate`) values ";
						if($jmlid > 0){					
							$arrList = array();
							foreach($assignmentto as $valassignedto)
							{				
								$arrList[] = "('".$id."',
												'".$departementto."',
												'".$jabatanto."',
												'".$valassignedto."',
												'".$startdate."',
												'".$targetdate."',
												'1',
												'".$_SESSION['standard']['userid']."',
												'".$datenow."')";
							}
							$str .= implode(",",$arrList).";";
						}else{
							$str .= "('".$id."',
									'".$departementto."',
									'".$jabatanto."',
									'',
									'".$startdate."',
									'".$targetdate."',
									'1',
									'".$_SESSION['standard']['userid']."',
									'".$datenow."')";
						}
						
						try{
							$owlPDO->exec($str);
						}catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>"; 
							die(); 
						}
					}
					catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>"; 
						die(); 
					}
				}
			}else{
				$err = "Data Belum lengkap";
			}
		}else{
			$err = "Data not Found";
		}
	break;
	case 'updateassignedto':
		$id = "";
		$userid = "";
		$where = "";
		if(isset($_POST['id'])){
			$userid =$_POST['id'];
		}
		if(isset($_POST['parentid'])){
			$id =$_POST['parentid'];
		}
		if($id != ""){
			$where .= "and id='".$id."'";
		}
		
		$strCheck="select status from ".$dbname.".`sdm_taskassignment` where 1=1 ".$where." limit 1";
		$r=$owlPDO->query($strCheck) or die(print " Gagal: ".PDOException::getMessage());
		$r->setFetchMode(PDO::FETCH_ASSOC);
		$res = $r->fetch();
		if($res['status'] <= 1){
			$str="UPDATE ".$dbname.".`sdm_taskassignment` set assignedto = '".$userid."' where status in ('0','1') ".$where;
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e) {
				$err = " Gagal  !: " . $e->getMessage();				
				die();
			}
		}else{
			$err = "Tidak bisa dirubah apabila status Assigment sudah bukan open lagi";
		}
	break;
	case 'updatestatus':
		$id = "";
		$status = "";
		$where = "";
		if(isset($_POST['id'])){
			$status =$_POST['id'];
		}
		if(isset($_POST['parentid'])){
			$id =$_POST['parentid'];
		}
		if($id != ""){
			$where .= "and id='".$id."'";
		}

		$str="UPDATE ".$dbname.".`sdm_taskassignment` set status = '".$status."' where 1=1 ".$where;
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e) {
			$err = " Gagal  !: " . $e->getMessage();	
			die(); 
		}
	break;
	case 'delete':
		if(isset($_POST['id'])){
			$parentid =$_POST['id'];
		}else{
			$parentid = "";
		}
		if(isset($_POST['detailid'])){
			$id =$_POST['detailid'];
		}else{
			$id = "";
		}
		$where = "";
		if($id != ""){
			$where .= "and id='".$id."'";
		}
		if($parentid != ""){
			$where .= "and parentid = '".$parentid."'";
		}
		$str="UPDATE ".$dbname.".`sdm_taskassignmentdt` set isactive = '0' where 1=1 ".$where;
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
	case 'viewtask':
		if(isset($_POST['id'])){
			$id =$_POST['id'];
		}else{
			$id = "";
		}
		if(isset($_POST['subject'])){
			$subject =$_POST['subject'];
		}else{
			$subject = "";
		}
		$hal = 1;
		if(isset($_GET['hal'])){
			$hal	= $_GET['hal'];
		}
		// jika view memiliki flag
		$where = "";
		if($id != ""){
			$id = $_POST['id'];
			$where .= " and a.id='".$id."'";
		}
		if($subject != ""){
			$where .= " and a.subject='".$subject."'";
		}
		$sql = "select datakaryawan.karyawanid, datakaryawan.nik,datakaryawan.namakaryawan,datakaryawan.bagian, sdm_5tipekaryawan.tipe
				 from " . $dbname . ".datakaryawan
				 left join ".$dbname.".sdm_5tipekaryawan on sdm_5tipekaryawan.id = datakaryawan.tipekaryawan
					   where sdm_5tipekaryawan.no <= 5
					   ORDER BY sdm_5tipekaryawan.no, datakaryawan.namakaryawan ASC";
		//echo $sql;
		//exit("ERROR:".$where);
		// $sql = "select karyawanid,nik,namakaryawan,bagian from " . $dbname . ".datakaryawan 
		// 	   where lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "'
		// 	   ORDER BY `namakaryawan` ASC";
		$datakaryawan=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$datakaryawan->setFetchMode(PDO::FETCH_ASSOC);
		$karyawan = array(); ?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['taskassignment'] ?></legend>
<?php
		while ($r = $datakaryawan->fetch()) {
			$data['karyawanid'] 	= $r['karyawanid'];
			$data['nik'] 			= $r['nik'];
			$data['namakaryawan'] 	= $r['namakaryawan'];
			$data['bagian'] 	  	= $r['bagian'];
			$karyawan[]	 			= $data;
		}
		//================ PAGING ========================//	
			$limit = 20;//-Paging-
			$halaman_aktif = $hal; //-Paging-Phalaman saat ini
			$p = new Paging; // -Paging- Class paging
			$posisi = $p->cariPosisi($limit,$halaman_aktif);// -Paging- Posisi Data	
			
			$jmlDt = "select a.*,c.subject,c.description,c.notes,b.namakaryawan, d.nama, e.namajabatan 
				  from ".$dbname.".sdm_taskassignment a
				  left join ".$dbname.".datakaryawan b on b.karyawanid = a.assignedto
				  left join ".$dbname.".sdm_taskdocument c on c.id = a.iddoc
				  left join ".$dbname.".sdm_5departemen d on d.kode = a.departementto
				  left join ".$dbname.".sdm_5jabatan e on e.kodejabatan = a.jabatanto
				  where (( case 
					  when a.assignedto='0000000000' and  a.departementto != ''
					  then 
					 	 a.departementto = '".$_SESSION['empl']['bagian']."'
					  when a.assignedto='0000000000' and  a.jabatanto != ''
					  then 
					 	 a.jabatanto = '".$_SESSION['empl']['jabatan']."'
					  else 
					  	a.assignedto = '".$_SESSION['standard']['userid']."' 
					  end
					  ) and a.status in ('1','2','3') ".$where." ) 
				  or (a.createby ='".$_SESSION['standard']['userid']."' and a.status in ('0','1','2','3') ".$where." )";
			$rjml = fetchData($jmlDt);
			$jmldata = count($rjml);
			
			$html = "";
			$str="select a.*,c.subject,c.description,c.notes,b.namakaryawan, d.nama, e.namajabatan
				  from ".$dbname.".sdm_taskassignment a
				  left join ".$dbname.".datakaryawan b on b.karyawanid = a.assignedto
				  left join ".$dbname.".sdm_taskdocument c on c.id = a.iddoc
				  left join ".$dbname.".sdm_5departemen d on d.kode = a.departementto
				  left join ".$dbname.".sdm_5jabatan e on e.kodejabatan = a.jabatanto
				  where ((
					  case 
					  when a.assignedto='0000000000' and  a.departementto != ''
					  then 
					 	 a.departementto = '".$_SESSION['empl']['bagian']."'
					  when a.assignedto='0000000000' and  a.jabatanto != ''
					  then 
					 	 a.jabatanto = '".$_SESSION['empl']['jabatan']."'
					  else 
					  	a.assignedto = '".$_SESSION['standard']['userid']."' 
					  end 
				  ) and a.status in ('1','2','3') ".$where.") 
				  or (a.createby ='".$_SESSION['standard']['userid']."' and a.status in ('0','1','2','3') ".$where." ) 
				  order by a.status ASC,a.createdate DESC
				  LIMIT $posisi,$limit ";
				  
			$jml = $p->jumlahHalaman($jmldata,$limit);//-Paging- jumlah data
			
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$no = 1;
		?>
		<table class="sortable" border="0" cellspacing="1" width="100%">
		<thead>
			<tr>
				<th width="1">
					No
				</th>
				<th width="200">
					<?php echo $_SESSION['lang']['subject'] ?>
				</th>
				<th width="100">
					<?php echo $_SESSION['lang']['assignmentto'] ?>
				</th>

				<th width="90px">
					<?php echo 'departement' ?>
				</th>

				<th width="100">
					<?php echo 'jabatan' ?>
				</th>

				<th width="100">
					<?php echo $_SESSION['lang']['startdate'] ?>
				</th>
				<th width="100">
					<?php echo $_SESSION['lang']['targetdate'] ?>
				</th>
				<th width="100">
					<?php echo $_SESSION['lang']['status'] ?>
				</th>
				<th width="100">
					Tanggal Buat
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
	
		<?php
		function selected($val,$param){
			$selected = "";
			if($val == $param){
				$selected = "selected";
			}
			return $selected;
		}
			while($bar=$res->fetch())
			{
				$status = "";
				switch ($bar->status){	
					case 0:
						$status = 'Cancel';
					break;
					case 1:
						$status = 'Open';
					break;
					case 2:
						$status = 'In Progress';
					break;
					case 3:
						$status = 'Complete';
					break;
				}
				$allestate="";
				$html .= "<tr>";
				$html .= "<td class=rowcontent align=left>".$no."</td>";
				$html .= "<td class=rowcontent align=left>".$bar->subject."</td>";
				if($bar->createby == $_SESSION['standard']['userid'] or $bar->status == 1 or $bar->status == 2 or $bar->status == 3){
					$html .= "<td class=rowcontent align=left>";
					$html .= $bar->namakaryawan;
					if($bar->createby == $_SESSION['standard']['userid']){	
						if($bar->status == 1 & $bar->jabatanto == ""){
							$html .= "<select name='status' onchange=\"changedata(this,'".$bar->id."','updateassignedto');\">
							<option value=".$allestate.">-Seluruhnya-</option>";
							for($i=0; $i<count($karyawan); $i++){
								if($karyawan[$i]['bagian'] == $bar->departementto){
									if($karyawan[$i]['karyawanid'] == $bar->assignedto){
										$html.="
										<option value=" . $karyawan[$i]['karyawanid'] . " selected>" . $karyawan[$i]['namakaryawan'] . " - " . $karyawan[$i]['nik'] . "</option>";
									}else{
										$html.="<option value=" . $karyawan[$i]['karyawanid'] . ">" . $karyawan[$i]['namakaryawan'] . " - " . $karyawan[$i]['nik'] . "</option>";
									}
								}
							}
							$html .=" </select>";
						}
					}
					$html .="</td>";
				}else{
					$html .= "<td class=rowcontent align=left>".$bar->namakaryawan."</td>";
				}
				$html .= "<td class=rowcontent align=center>".$bar->nama."</td>";
				$html .= "<td class=rowcontent align=center>".$bar->namajabatan."</td>";
				$html .= "<td class=rowcontent align=center>".date("d-m-Y",strtotime($bar->startdate))."</td>";
				$html .= "<td class=rowcontent align=center>".date("d-m-Y",strtotime($bar->targetdate))."</td>";
				$html .= "<td class=rowcontent align=center>".$status."</td>";
				$html .= "<td class=rowcontent align=center>".date("d-m-Y",strtotime($bar->createdate))."</td>";
				if($id != ""){
					
					$html .= "<td class=rowcontent align=center>";
					if($bar->createby == $_SESSION['standard']['userid']){	
						if($bar->status == 0 or $bar->status == 3 ){
							$html .= $status;
						}else{
							$html .= "<select name='status' onchange=\"changedata(this,'".$bar->id."','updatestatus');\">";
							$html .=	"<option value='0' ".selected(0,$bar->status).">Cancel</option>";
							$html .=	"<option value='1' ".selected(1,$bar->status).">Open</option>";
							$html .=	"<option value='3' ".selected(3,$bar->status).">Complete</option>";
							$html .=	"</select>";
						}
					}else if($bar->assignedto == $_SESSION['standard']['userid']){
						if($bar->status == 0 or $bar->status == 2 or $bar->status == 3){
							$html .= $status;
						}else{
							$html .= "<select name='status' onchange=\"changedata(this,'".$bar->id."','updatestatus');\">";
							$html .=	"<option value='1' ".selected(1,$bar->status).">Open</option>";
							$html .=	"<option value='2' ".selected(2,$bar->status).">In Progress</option>";
							$html .=	"</select>";
						}
					}else if($bar->departementto == $_SESSION['empl']['bagian']){
						if($bar->status == 0 or $bar->status == 2 or $bar->status == 3){
							$html .= $status;
						}else{
							$html .= "<select name='status' onchange=\"changedata(this,'".$bar->id."','updatestatus');\">";
							$html .=	"<option value='1' ".selected(1,$bar->status).">Open</option>";
							$html .=	"<option value='2' ".selected(2,$bar->status).">In Progress</option>";
							$html .=	"</select>";
						}
					}else if($bar->jabatanto ==  $_SESSION['empl']['jabatan']){
						if($bar->status == 0 or $bar->status == 2 or $bar->status == 3){
							$html .= $status;
						}else{
							$html .= "<select name='status' onchange=\"changedata(this,'".$bar->id."','updatestatus');\">";
							$html .=	"<option value='1' ".selected(1,$bar->status).">Open</option>";
							$html .=	"<option value='2' ".selected(2,$bar->status).">In Progress</option>";
							$html .=	"</select>";
						}
					}
					$html .="</td>";
				}else{
					$html .= "<td class=rowcontent align=center><a href=\"#\" onclick=\"exacData('".$bar->id."','taskdetail');\"> View</a></td>";
				}
				$html .= "</tr>";
				
				$no++;
			}
		
		if($no == 1){
			echo "<tr><td colspan='8'> No Data </td></tr>";
		}else{
			echo $html;
		}?>
		</tbody>
		<?php if($id == ""){ ?>
		<tfoot>
			<tr>
				<td colspan="10" align="center">
				<?php 
					//insert Attribute action ex: href/onclick/onchange/etc..
					$buttonaction = array(
						'first' =>	'onclick="exacData(\'\',\'viewtask&hal=1\');"',
						'prev' 	=> 	'onclick="exacData(\'\',\'viewtask&hal='.($halaman_aktif-1).'\')"',
						'next' 	=> 	'onclick="exacData(\'\',\'viewtask&hal='.($halaman_aktif+1).'\')"',
						'last' 	=> 	'onclick="exacData(\'\',\'viewtask&hal='.($jml).'\')"',
						'pages'	=> 	'onchange="exacData(\'\',\'viewtask&hal=\'+this.value);"'
					);
					echo $p->navHalaman($halaman_aktif,$jml,$buttonaction); //-Paging- Create Element Nav halaman; 
				?>
				</td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>
</fieldset>
		<?php
	break;
	case 'viewtaskdetail':
		if(isset($_POST['id']) and $_POST['id'] != ""){
			$id =$_POST['id'];
			
			// jika view memiliki flag
			$where = "";
			$where2 = "";
			if($id != ""){
				$id = $_POST['id'];
				$where .= "parentid='".$id."' and isactive = '1'";
				$where2 .= "id='".$id."'";
			}
			$head="select a.*,c.subject,c.description,c.notes,b.namakaryawan from ".$dbname.".sdm_taskassignment a
				  left join ".$dbname.".datakaryawan b on b.karyawanid = a.createby
				    left join ".$dbname.".sdm_taskdocument c on c.id = a.iddoc
				  where a.id='".$id."' limit 1";
			$resHead=$owlPDO->query($head) or die(print " Gagal: ".PDOException::getMessage());
			$resHead->setFetchMode(PDO::FETCH_OBJ);
			$r = $resHead->fetch();

			//Form Detail and Comment
			$str="select a.*,b.namakaryawan from ".$dbname.".sdm_taskassignmentdt a
				  left join ".$dbname.".datakaryawan b on b.karyawanid = a.createby
				  where ".$where;
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$html = "<br/><button id='backpage' class=mybutton href='#' onclick=\"exacData();\" style='float:left;'><span>Back</span></button>";
			$html .= "<button id='refresh' class=mybutton href='#' onclick=\"exacData('".$id."','taskdetail');\" style='float:left;margin-left:10px;'><span>Refresh</span></button>";
			$html .= "<br/><br/><fieldset>";
			$html .= "<legend>".$r->namakaryawan."</legend>";
			$html .= "<div>".$r->description."</div>";
			$html .= "<div>Catatan: ".$r->notes."</div>";
			$html .= "</fieldset>";
			$html .= "<br/><fieldset style='position: relative; min-height:100px;background:#f1f8fd;'><div style='margin-bottom:20px;'>";
			$no = 1;
			while($bar=$res->fetch())
			{
				$html .= "<fieldset style='margin-bottom:10px;'>";
				$html .= "<legend>".$bar->namakaryawan."</legend>";
				$html .= "<div id='quotewrap".$bar->id.$bar->parentid."' class='quotewrap'>".$bar->description."</div>";
				$html .= "<div>";
				if($bar->createby == $_SESSION['standard']['userid']){
				$html .= "<a class='pointer' onclick=\"deleteComment('".$bar->id."','".$bar->parentid."');\" style='margin-left:10px;'><font size='1'>DELETE</font></a>";
				}
				$html .= "<a class='pointer' onclick=\"quoteAct('quotewrap".$bar->id.$bar->parentid."');\" style='margin-left:10px;'><font size='1'>QUOTE</font></a>";
				$html .= "<font size='1' style='margin-left:10px;'>".date("d F Y",strtotime($bar->createdate))."</font>";
				$html .= "</div></fieldset>";
				$no++;
			}
			$html .= "</div>";
			//form comment
			$html .= '<form name="taskassignmentform" method="POST" action="sdm_slave_taskassignment.php?switch=insertcomment" onsubmit="saveform(this,commentSuccess);return false;">
						<div style="">
						<div class="title">Comment :</div>
						<input type="hidden" name="parentid" value="'.$id.'">
						<textarea id="isicomment" name="isi"  style="width:98%;padding:5px 1%;"></textarea>
						<button type="submit" class="mybutton">'.$_SESSION['lang']['save'].'</button>
					  </div>
					  </form>';
			$html .= "</fieldset>";
			echo $html;
		}else{
			$sql = "select distinct a.bagian, c.kode, c.nama from ".$dbname.".datakaryawan a
					left join ".$dbname.".sdm_5tipekaryawan b on b.id = a.tipekaryawan
					left join ".$dbname.".sdm_5departemen c on c.kode = a.bagian
					where b.no <= 5
					order by c.nama ASC";
			// $sql = "select * from " . $dbname . ".sdm_5departemen where kode in (select bagian from datakaryawan) ORDER BY nama";
			$departement = fetchData($sql);
			$optDepartement = "";
			foreach($departement as $v){
				$optDepartement.="<option value=".$v['kode'].">".$v['nama']."</option>";
			}

			$sqljbtn = "select distinct datakaryawan.kodejabatan, sdm_5jabatan.namajabatan from " . $dbname . ".datakaryawan
			left join ".$dbname.".sdm_5jabatan on sdm_5jabatan.kodejabatan = datakaryawan.kodejabatan
			left join ".$dbname.".sdm_5tipekaryawan on datakaryawan.tipekaryawan = sdm_5tipekaryawan.id
			where sdm_5tipekaryawan.no <= 5
			ORDER BY namajabatan";
			$jabatan = fetchData($sqljbtn);
			$optJabatan = "";
			foreach($jabatan as $vj){
				$optJabatan.="<option value=".$vj['kodejabatan'].">".$vj['namajabatan']."</option>";
			}

			//Form Utama
			
			?>

			<fieldset>
				<legend><?php echo $_SESSION['lang']['input'] ?> <?php echo $_SESSION['lang']['taskassignment'] ?></legend>
				<form name="taskassignmentform" method="POST" action="sdm_slave_taskassignment.php?switch=insert" onsubmit="saveform(this,askSuccess);return false;">
				<table cellspacing="1" border="0" style="width:1000px;">
					<tr>
						<td><?php echo "Berdasarkan" ?></td><td>:</td>
						<td>
							<input id="dept_radio" type="radio" name="pilihan" value="departement" onclick="pilih(0);" checked>Departemen</input>
							<input id="jab_radio" type="radio" name="pilihan" value="jabatan" onclick="pilih(1);" >Jabatan</input>
							<input id="all_radio" type="radio" name="pilihan" value="all" onclick="pilih(2);" >All</input>
						</td>
					</tr>
					<tr>
						<td id="caption_sending" style="display: block;"><?php echo "Departemen" ?></td>
						<td>
							<text id="tanda" style="display: block;">:</text>
						</td>
						<td>
						<select id="departementto" name="departementto" style="max-width:500px;width:100%;display: block;"  onchange="changeOpt('departement='+this.value,'switch=getkaryawan','assignmentto')">
							<option value="">- Choose -</option>
							<?php echo $optDepartement; ?>
						</select>

						<select id="jabatanto" name="jabatanto" style="max-width:500px;width:100%;display: none;"  onchange="changeOpt('kodejabatan='+this.value,'switch=getkaryawan','assignmentto')" >
							<option value="">- Choose -</option>
							<?php echo $optJabatan; ?>
						</select>

						</td>
					</tr>
					<tr>
						<td><?php echo $_SESSION['lang']['assignmentto'] ?></td><td>:</td>
						<td id="datalist_both">
						<datalist id="assignmentto" style="max-width:500px;width:100%;" >
						</datalist>
						 <span onclick="getlistdatakarywan('assignmentto',event);"><img class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">  Pilih Karyawan </span>
						</td>
					</tr>
					<tr>
						<td><?php echo $_SESSION['lang']['subject'] ?></td><td>:</td>
						<td><input type='text' class='myinputtext' name="subject" size='10' maxlength='35' style="max-width:500px;width:100%;" /></td>
					</tr>
					<tr>
						<td><?php echo $_SESSION['lang']['startdate'] ?></td><td>:</td>
						<td><input type='text' class='myinputtext' name="startdate" size='10' maxlength='35' onmousemove="setCalendar(this);return false;" readonly /></td>
					</tr>
					<tr>
						<td><?php echo $_SESSION['lang']['targetdate'] ?></td><td>:</td>
						<td><input type='text' class='myinputtext' name="targetdate" size='10' maxlength='35' onmousemove="setCalendar(this);return false;" readonly /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					<tr>
						<td valign="top"><?php echo $_SESSION['lang']['isi'] ?></td><td valign="top">:</td>
						<td><textarea id="isi" name="isi"  style="max-width:700px;width:100%;"></textarea></td>
					</tr>
					<tr>
						<td valign="top"><?php echo $_SESSION['lang']['note'] ?></td><td valign="top">:</td>
						<td ><input type='text' class='myinputtext' id="note" name="note" size='10' maxlength='35' style="max-width:500px;width:100%;" /></td>
					<tr>
					<tr>
						<td colspan="3" id="tmblHeader">
							<button type="submit" class="mybutton"><?php echo $_SESSION['lang']['save'] ?></button>
							<button type="button" class="mybutton" onclick="exacData();"><?php echo $_SESSION['lang']['cancel'] ?></button>
						</td>
					</tr>
				</table>
				</form>
			</fieldset>
		<?php	
		}
	break;
	case 'getkaryawan':
		$where = " a.bagian = ''";
		if(isset($_GET['departement'])){
			$departement = $_GET['departement'];
			$where = " bagian = '".$departement."' and";
		}else if(isset($_GET['kodejabatan'])){
			$kodejabatan = $_GET['kodejabatan'];
			$where = " kodejabatan = '".$kodejabatan."' and";
		}else{
			$where = "";
		}

		$sql = "select a.karyawanid,a.namakaryawan,a.lokasitugas,b.tipe from " . $dbname . ".datakaryawan a
				left join ".$dbname.".sdm_5tipekaryawan b on b.id = a.tipekaryawan
			   where ".$where." b.no <= 5
			   ORDER BY b.no,a.namakaryawan ASC";
		// $sql = "select karyawanid,namakaryawan,lokasitugas from " . $dbname . ".datakaryawan
		// where ".$where."
		// order by namakaryawan ASC";
		$query = fetchData($sql);
			//$optOrg = "<option value=''>- Choose -</option>";
			foreach($query as $v)
			{
				$optOrg.="<option value=" . $v['karyawanid'] . ">" . strtoupper($v['namakaryawan']) . " - " . $v['tipe'] . " - " .$v['lokasitugas']. "</option>";
			}
		echo $optOrg;
			
	
	break;
	case 'insertcomment':
		if(isset($_POST)){
			$data =$_POST;
		}else{
			$data = "";
		}
		if($data != ""){
			if($_POST['isi'] != "" ){
				$parentid		= $_POST['parentid'];
				$description	= $_POST['isi'];
				$datenow		= date('Y-m-d');
				
				$str="INSERT INTO ".$dbname.".`sdm_taskassignmentdt` 
					(`parentid`,`description`,`createby`,`createdate`)
				values ('".$parentid."',
				'".$description."',
				'".$_SESSION['standard']['userid']."',
				'".$datenow."')";
				try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>"; 
					die(); 
				}
			}else{
				$err = "Data Belum lengkap";
			}
		}else{
			$err = "Data not Found";
		}
	break;
}

if($err == ""){
	
}else{
	echo "ERROR: ".$err;
}
?>
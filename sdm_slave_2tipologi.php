<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
use Dompdf\Dompdf;

$folder = "imgbot/temp/";

$param = $_POST;
if(count($param)==0){$param = $_GET;	}
$id       = checkPostGet('id','');
$method   = checkPostGet('method','');
$tipe     = checkPostGet('tipe','');
$nama     = checkPostGet('nama','');
$dept     = checkPostGet('dept','');
$tglnilai = tanggalsystemn(checkPostGet('tglnilai',''));
$tipeprint= checkPostGet('tipeprint','');

$arrtext[1]['A']='mengarahkan<br>(mengatur)';
$arrtext[1]['B']='mempengaruhi<br>(meyakinkan)';
$arrtext[1]['C']='mantap-tenang';
$arrtext[1]['D']='hati-hati';
$arrtext[2]['A']='pasti';
$arrtext[2]['B']='optimistis';
$arrtext[2]['C']='sabar';
$arrtext[2]['D']='menahan diri';
$arrtext[3]['A']='berani';
$arrtext[3]['B']='antusias';
$arrtext[3]['C']='menstabilkan<br>(menenangkan)';
$arrtext[3]['D']='analitis';
$arrtext[4]['A']='suka bersaing<br>(kompetitif)';
$arrtext[4]['B']='suka bicara';
$arrtext[4]['C']='menampung';
$arrtext[4]['D']='persis-akurat';
$arrtext[5]['A']='kuat-tegas';
$arrtext[5]['B']='mempesona<br>(tampil menarik)';
$arrtext[5]['C']='santai';
$arrtext[5]['D']='ingin tahu';

switch ($method) {
	case 'getDept':
		$str = "SELECT * FROM ".$dbname.".datakaryawan WHERE karyawanid='".$nama."'";
		$res = fetchdata($str);

		echo $res[0]['bagian']."####".$res[0]['kodejabatan']."####".$res[0]['lokasitugas'];
	break;
	case 'insert':
		try {
            $owlPDO->beginTransaction();
			
			$str = "SELECT * FROM ".$dbname.".sdm_tipologi WHERE karyawanid='".$param['karyawanid']."' and tanggal='".tanggalsystemn($param['tglnilai'])."'";
			// exit("error".$str);
			$res = fetchdata($str);
			foreach($res as $bar){
				$posting=$bar['posting'];
				$param['idht']=$bar['idht'];
			}
			if(count($res)>0){
				if($posting=='1'){
					throw new PDOException("Data sudah diposting.");
				}
				$data = array(
					'karyawanid' =>$param['karyawanid'],
					'jabatan'    =>$param['jabatan'],
					'dept'       =>$param['dept'],
					'lokasitugas'=>$param['lokasitugas'],
					'divisi'     =>getKary($param['karyawanid'],'subbagian'),
					'tanggal'    =>tanggalsystemn($param['tglnilai']),
					'createdby'  =>$_SESSION['standard']['userid'],
					'createdtime'=>date('Y-m-d H:i:s'),
					'updateby'   =>$_SESSION['standard']['userid']
				);

				$where = "idht='".$param['idht']."'";
				$query = updateQuery($dbname,'sdm_tipologi',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
			}else{				
				$data = array(
					'karyawanid' =>$param['karyawanid'],
					'jabatan'    =>$param['jabatan'],
					'dept'       =>$param['dept'],
					'lokasitugas'=>$param['lokasitugas'],
					'divisi'     =>getKary($param['karyawanid'],'subbagian'),
					'tanggal'    =>tanggalsystemn($param['tglnilai']),
					'createdby'  =>$_SESSION['standard']['userid'],
					'createdtime'=>date('Y-m-d H:i:s'),
					'updateby'   =>$_SESSION['standard']['userid']
				);

				$queryH = insertQuery($dbname,'sdm_tipologi',$data,array_keys($data)); #exit("error".$queryH);
				$owlPDO->exec($queryH);
				
				$str = "SELECT * FROM ".$dbname.".sdm_tipologi WHERE karyawanid='".$param['karyawanid']."' and tanggal='".tanggalsystemn($param['tglnilai'])."'";
				$res = fetchdata($str);
				$param['idht']=$res[0]['idht'];
			}
			
         
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warningcode : " . addslashes($e->getMessage());
        }
		echo $param['idht'];
	break;
	case'simpandt':
		try {
            $owlPDO->beginTransaction();
			
			$str = "delete from ".$dbname.".sdm_tipologidt WHERE 1=1 and idht = '".$param['idht']."'";
			$owlPDO->exec($str);
			$total=0;
			foreach($param['nilai'] as $row => $v1){
				foreach($v1 as $col => $nilai){
					$data = array(
						'idht'       =>$param['idht'],
						'kolom'      =>$col,
						'baris'      =>$row,
						'nilai'      =>$nilai,
						'createdby'  =>$_SESSION['standard']['userid'],
						'createdtime'=>date('Y-m-d H:i:s'),
						'updateby'   =>$_SESSION['standard']['userid']
					);
					
					$total+=$nilai*2;
					$queryH = insertQuery($dbname,'sdm_tipologidt',$data,array_keys($data)); #exit("error".$queryH);
					$owlPDO->exec($queryH);
				}
			}
			if($total!=100){
				throw new PDOException("Jumlah salah, proses dibatalkan.");
			}
			
		$owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warningcode : " . addslashes($e->getMessage());
        }
	break;
	case'loaddatadetail':
		$tab="<label style=font-weight:bold;color:blue;>Di ruangan yang disediakan di bawah, identifikasilah perilaku dalam pengertian bagaimana kecocokannya dengan ciri khas Anda. disetiap deret berikan nilai :</label>";
		$tab.="<ul><label style=font-weight:bold;color:red;>4 - untuk perilaku yang paling sesuai dengan Anda.</label></ul>";
		$tab.="<ul><label style=font-weight:bold;color:red;>3 - untuk perilaku paling sesuai tingkat berikutnya.</label></ul>";
		$tab.="<ul><label style=font-weight:bold;color:red;>2 - untuk tingkat lebih rendah.</label></ul>";
		$tab.="<ul><label style=font-weight:bold;color:red;>1 - untuk perilaku yang paling sedikit kesesuaiannya dengan Anda.</label></ul>";
		
		$tab.="<table><tr><td>";
		$tab.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center colspan=2>Kolom A</th>
						<th align=center colspan=2>Kolom B</th>
						<th align=center colspan=2>Kolom C</th>
						<th align=center colspan=2>Kolom D</th>
					</tr>
				</thead>
				<tbody>";
			$str = "select * from ".$dbname.".sdm_tipologidt where idht='".$param['idht']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$nilai[$bar['baris']][$bar['kolom']]=$bar['nilai'];
				$ttlcolom[$bar['kolom']]+=$bar['nilai'];
				$ttlbaris[$bar['baris']]+=$bar['nilai'];
			}
			
			$arrcol=array('A','B','C','D');
			$arrrow=range(1,5);
			$arropt=range(1,4);
			
			foreach($arrrow as $row){
				$tab.="<tr class=rowcontent id=rowdt".$no.">";
				$tab.="<td align=center>".$row."</td>";
				foreach($arrcol as $col){
					$optisi = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
					foreach($arropt as $val){
						$s="";
						if($nilai[$row][$col]==$val){
							$s="selected";
						}
						$optisi.="<option value='".$val."' ".$s.">".$val."</option>";
					}
					
					$tab.="<td align=center><select id='isi_".$row."_".$col."' style='width:90px;height:30px' onchange=\"cekisi(this.value,'".$row."','".$col."');\">".$optisi."</select></td>";					
					$tab.="<td align=center style=font-weight:bold;>".ucfirst($arrtext[$row][$col])."</td>";					
				}
				$tab.="<td hidden><input id=total_".$row." value=".$ttlbaris[$row]."></td>";
				$tab.="</tr>";
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center rowspan=2></td>";
				foreach($arrcol as $col){
					$tab.="<td align=center><input disabled class=myinputtextnumber style='width:85px;height:30px;text-align:center;font-size:14px' id=jumlah_".$col." value=".$ttlcolom[$col]."></td>";
					$tab.="<td align=center  style=font-weight:bold;>Jumlah</td>";					
				}
			$tab.="</tr>";
			
			$tab.="<tr class=rowcontent>";
				foreach($arrcol as $col){
					$tab.="<td align=center><input disabled class=myinputtextnumber style='width:85px;height:30px;text-align:center;font-size:14px' id=jumlah2_".$col." value=".($ttlcolom[$col]*2)."></td>";
					$tab.="<td align=center style=font-weight:bold;>X2</td>";					
				}
			$tab.="</tr>";
		
			$tab.="</tbody>";
			$tab.="</table>";
			$tab.="</td>";
			$tab.="<td>";
			$tab.="<div id=graph></div>";
			$tab.="</td>";
			$tab.="</tr>
					<tr>
						<td align=center><button class=mybutton onclick=\"simpandt();\">".$_SESSION['lang']['save']."</button></td>
					</tr>
				</table>";
			
			$tab.="<input id=idht type=hidden value=".$idht."></tr>
			";
			
			
			
		echo $tab;
	break;
	
	case 'hapus':
		$where = " and idht='".$param['id']."'";
		$str = "delete from ".$dbname.".sdm_tipologi WHERE 1=1 ".$where."";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'loaddata':
		$where = "";
		if ($nama != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$nama."%')";
		}
		if ($dept != '') {
			$where .= " AND dept='".$dept."'";
		}
		if ($thnnilai != ''){
			$where .= " AND tahun='".$thnnilai."'";
		}
		
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$wh = "";
			$where.=" and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh.")";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$wh = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
			$where.=" and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$wh.")";
		} else {
			$where.= " AND karyawanid='".$_SESSION['standard']['userid']."'";
		}
		
		

		$tab="<br><table border=0 cellspacing=1 cellpadding=5 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']."</th>
							<th align=center>".$_SESSION['lang']['nik2']."</th>
							<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
							<th align=center>".$_SESSION['lang']['lokasitugas']."</th>
							<th align=center>".$_SESSION['lang']['jabatan']."</th>
							<th align=center>".$_SESSION['lang']['departemen']."</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th align=center>".$_SESSION['lang']['updateby']."</th>
							<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
						</tr>
					</thead>
					<tbody>";

        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 16;

        $str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".sdm_tipologi WHERE 1=1 ".$where; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $jlhbrs = $bar['jmlhrow'];
        }
        

		$str = "SELECT * FROM ".$dbname.".sdm_tipologi
				WHERE 1=1 ".$where."
				ORDER BY idht DESC
				LIMIT ".$offset.",".$limit;
		$res = fetchdata($str);

        $no = $offset+1;
		foreach($res as $key=>$val){
			$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".tanggalnormal($val['tanggal'])."</td>
					<td>".getKary($val['karyawanid'],'nik')."</td>
					<td>".getKary($val['karyawanid'],'namakaryawan')."</td>
					<td>".getNamaOrg(getKary($val['karyawanid'],'lokasitugas'))."</td>
					<td>".getNamaJabatan($val['jabatan'])."</td>
					<td>".getNamaDept($val['dept'])."</td>
					<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
					<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
					
					
					if($val['posting']=='1'){
						$tab.="<td align=center width=30px></td>";
						$tab.="<td align=center width=30px></td>";
						$tab.="<td align=center width=30px><img src='images/skyblue/posted.png' class='zImgBtn' title='Posted'></td>";
					}else{						
						$tab.="<td align=center width=30px>
							<img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"fillField('".$val['idht']."','".$val['karyawanid']."','".$val['jabatan']."','".$val['dept']."','".tanggalnormal($val['tanggal'])."','".getKary($val['karyawanid'],'lokasitugas')."');\">
						</td>";
						$tab.="<td align=center width=30px>
							<img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedata('".$val['idht']."');\">
						</td>";
						$tab.="<td align=center width=30px>
							<img src='images/skyblue/posting.png' class='zImgBtn' title='Posting' onclick='posting(".$val['idht'].");'>
						</td>";
					}
					
					$tab.="<td align=center width=30px>
						<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['idht']."');\">
					</td>
					<td align=center width=30px>
						<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['idht']."');\">
					</td>
				</tr>";
            $no += 1;
		}
		
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		

		$tab .= "</tbody></table>";

		echo $tab;
	break;
	case 'posting':
			$str = "SELECT * FROM ".$dbname.".sdm_tipologidt WHERE idht = ".$param['id']; 
			$res = fetchdata($str);
			if(count($res)==0){
				exit("error : Detail belum ada.");
			}
			$data = array(
				'posting'   => '1',
				'lastupdate'=> date("Y-m-d H:i:s"),
				'updateby'  => $_SESSION['standard']['userid']
			);
			$where = "idht = '".$param['id']."'";
			$query = updateQuery($dbname,'sdm_tipologi',$data,$where); //exit("error".$query);
			$owlPDO->exec($query);
	break;
	
	case'detail':
		
		$str = "SELECT * FROM ".$dbname.".sdm_tipologi WHERE idht = ".$param['id']; 
        $res = fetchdata($str)[0];
		$filename=$folder.$res['karyawanid'].".jpg";
		
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=0 style='font-size:13px'>";
		}else{			
			$tab.="<table border=0>";
		}
		$tab.="<tr>
				<td>".$_SESSION['lang']['nama']."</td>
				<td>:</td>
				<td colspan=4>".getKary($res['karyawanid'],'namakaryawan')."</td>
				
				<td width=50px></td>
				
				<td>".$_SESSION['lang']['jabatan']."</td>
				<td>:</td>
				<td colspan=8>".getNamaJabatan($res['jabatan'])."</td>
			</tr>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['lokasitugas']."</td>
				<td>:</td>
				<td colspan=4>".getNamaOrg(getKary($res['karyawanid'],'lokasitugas'))."</td>
				
				<td></td>
				
				<td>".$_SESSION['lang']['departemen']."</td>
				<td>:</td>
				<td colspan=8>".getNamaDept($res['dept'])."</td>
			</tr>
			<tr>
				<td>Tanggal</td>
				<td>:</td>
				<td colspan=8>".tanggalnormal($res['tanggal'])."</td>
			</tr>";
		$tab.="</table>";
		
		$tab.="<table><tr><td>";
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=5 class=sortable style='font-size:13px'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=7 class=sortable>";
		}
	
		$tab.="
				<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center colspan=2>Kolom A</th>
						<th align=center colspan=2>Kolom B</th>
						<th align=center colspan=2>Kolom C</th>
						<th align=center colspan=2>Kolom D</th>
					</tr>
				</thead>
				<tbody>";
			$str = "select * from ".$dbname.".sdm_tipologidt where idht='".$param['id']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$nilai[$bar['baris']][$bar['kolom']]=$bar['nilai'];
				$ttlcolom[$bar['kolom']]+=$bar['nilai'];
				$ttlbaris[$bar['baris']]+=$bar['nilai'];
			}
			
			$arrcol=array('A','B','C','D');
			$arrrow=range(1,5);
			$arropt=range(1,4);
			
			foreach($arrrow as $row){
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$row."</td>";
				foreach($arrcol as $col){
					$tab.="<td align=center>".$nilai[$row][$col]."</td>";					
					$tab.="<td align=center style=font-weight:bold;min-width:100px>".ucfirst($arrtext[$row][$col])."</td>";					
				}
				$tab.="</tr>";
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center rowspan=2></td>";
				foreach($arrcol as $col){
					$tab.="<td align=center width=30px>".$ttlcolom[$col]."</td>";
					$tab.="<td align=center  style=font-weight:bold;>Jumlah</td>";					
				}
			$tab.="</tr>";
			
			$tab.="<tr class=rowcontent>";
				foreach($arrcol as $col){
					$tab.="<td align=center>".($ttlcolom[$col]*2)."</td>";
					$tab.="<td align=center style=font-weight:bold;>X2</td>";					
				}
			$tab.="</tr>";
		
			$tab.="</tbody>";
			$tab.="</table>";
			$tab.="</td>";
			$tab.="<td>";
			if($param['tipeprint']=='pdf'){
				$stfieldset="style=\"border:#000000 solid 1px;padding: 5px 5px;font-size:12px;font-weight:lighter;cursor:auto;text-decoration:none;text-shadow:none;\"";
				$style="style=width:315px;height:260px";
				$tab.="<fieldset ".$stfieldset."><img src=".$filename." ".$style."></fieldset>";
			}else{				
				$tab.="<div id=graph></div>";
			}
			$tab.="</td>";
			$tab.="</table>";
		
		if($param['tipeprint']=='pdf'){
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("tipologi", array("Attachment" => false));
		}else{			
			echo $tab;
		}
	break;
	case'getgraph':
		if($param['id']!=''){			
			$str = "select * from ".$dbname.".sdm_tipologi where idht='".$param['id']."'";
			$res = fetchdata($str);
			if($param['jenis']=='pdf'){					
				$karyawanid=$folder.$res[0]['karyawanid'].".jpg";
			}
			
			$str = "select * from ".$dbname.".sdm_tipologidt where idht='".$param['id']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$ttlcolom[$bar['kolom']]+=$bar['nilai'];
			}
			$param['a']=$ttlcolom['A'];
			$param['b']=$ttlcolom['B'];
			$param['c']=$ttlcolom['C'];
			$param['d']=$ttlcolom['D'];
		}
		
		$datay1 = array($param['a'],$param['b'],$param['c'],$param['d']);
		
		// print_r($param);
		// exit("error");
		// Setup the graph
		$graph = new Graph(350,285);
		$graph->SetScale("textlin");

		// $theme_class=new UniversalTheme;

		// $graph->SetTheme($theme_class);
		// $graph->img->SetAntiAliasing(false);
		// // $graph->title->Set('Filled Y-grid');
		// $graph->SetBox(false);

		// $graph->SetMargin(40,20,36,63);

		// $graph->img->SetAntiAliasing();

		$graph->yaxis->HideZeroLabel();
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);
		$graph->yaxis->title->SetMargin(10);
		$graph->yaxis->SetLabelMargin(15);
		$graph->yaxis->SetLabelAlign('right','center');
		
		$graph->xgrid->Show();
		$graph->xgrid->SetLineStyle("solid");
		$graph->xaxis->SetTickLabels(array('A','B','C','D'));
		$graph->xgrid->SetColor('#E3E3E3');

		// Create the first line
		$l2plot = new LinePlot($datay1);
		$graph->Add($l2plot);
		// $l2plot->SetWeight(1);
		// $l2plot->SetColor('darkgreen');
		// $l2plot->SetBarCenter();
		$l2plot->mark->SetType(MARK_FILLEDCIRCLE,'',2.0);
		$l2plot->mark->SetWeight(2);
		$l2plot->mark->SetWidth(5);
		$l2plot->mark->setColor("darkgreen");
		$l2plot->mark->setFillColor("darkgreen");
	

		$graph->legend->SetFrameWeight(1);

		// print_r($param);
		// exit("error");
		// Output line
		if($param['jenis']!=''){
			if (file_exists($karyawanid)){
				unlink($karyawanid);
			}
			//$graph->img->Stream($karyawanid);
			$graph->Stroke($karyawanid);			
		}else{
			
			$graph->StrokeCSIM();			
		}
	break;
}

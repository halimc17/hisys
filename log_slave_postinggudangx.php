<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');


$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
@$param['nilai']  =str_replace(",","",$param['nilai']);


$nmorg= makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$whtrans =" and ((tipetransaksi!='1' and post='0') or (tipetransaksi='1' and hasilpersetujuan1='1' and post='0')) ";
switch ($method) {
	case'getkodegudang':
		$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str="select * from ".$dbname.".organisasi where 1=1 and induk = '".$param['pt']."' order by induk asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$s="select * from ".$dbname.".organisasi where 1=1 and induk = '".$bar['kodeorganisasi']."' and substr(tipe,1,6)='GUDANG' order by induk asc ";
			$r=fetchdata($s);
			foreach($r as $b){
				$d=$b['induk'];
				if(in_array($b['induk'],getdetorglog())){					
					if($d!=$n){			
						$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
						$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
					}
						$optorg.="<option value=".$b['kodeorganisasi'].">".$b['kodeorganisasi']." - ".$b['namaorganisasi']."</option>";
					
					$n=$d;
					if($d!=$n){			
						$optorg.="</optgroup>";
					}
				}
			}
		}
		
		if($_SESSION['empl']['subbagian']!=""){	
			$optorg="";
			$str = "select * from ".$dbname.".kebun_5gudangtransaksi where 1=1 and afdeling ='".$_SESSION['empl']['subbagian']."' and status='1'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$optorg.="<option value=".$bar['kodegudang'].">".$bar['kodegudang']." - ".getNamaOrg($bar['kodegudang'])."</option>";
			}
		}

		echo $optorg;
		//exit("error");
	break;
	case'getlistnotif':
		$hasil=0;
		$where = "";
		if($param['pt']!=''){
			$where.=" and kodept = '".$param['pt']."'";
		}
		if($_SESSION['empl']['subbagian']!=""){	
			$str = "select * from ".$dbname.".kebun_5gudangtransaksi where 1=1 and afdeling ='".$_SESSION['empl']['subbagian']."' and status='1'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$gudangdiv[$bar['kodegudang']]=$bar['kodegudang'];
			}
			
			$where.=" and kodegudang in ('".implode("','",$gudangdiv)."')";
		}else if($param['gudang']!=''){
			$where.=" and kodegudang = '".$param['gudang']."'";
		}
		
		if($param['nodok']!=''){
			$where.=" and notransaksi like '%".$param['nodok']."%'";
		}
		if($param['nopo']!=''){
			$where.=" and nopo like '%".$param['nopo']."%'";
		}
		if($param['asal']!=''){
			$where.=" and gudangx = '".$param['asal']."'";
		}
		if($param['noref']!=''){
			$where.=" and notransaksireferensi like '%".$param['noref']."%'";
		}
		if($param['tanggal']!=''){
			$where.=" and tanggal = '".tanggalsystemn($param['tanggal'])."'";
		}
		if($param['tipe']!=''){
			$where.=" and tipetransaksi = '".$param['tipe']."'";
		}
		if($param['sumber']=='load'){
			$where = "";
		}
		
		$str="select count(notransaksi) as countdata from ".$dbname.".log_transaksiht where substr(kodegudang,1,4) in ('".implode("','",getdetorglog())."') ".$whtrans." ".$where."";
		
		$res=fetchdata($str);
		$hasil=$res[0]['countdata'];
		
		
		echo $hasil;
	break;
	
	case'listposting':
		$tab="";
		$tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
			<caption style='color:blue;font-size:18px;font-weigh:bold'>POSTING</caption>
			<thead>
			<tr class='rowheader' style=height:25px>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['pt']."</th>
				<th align=center>".$_SESSION['lang']['sloc']."</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>
				<th align=center>".$_SESSION['lang']['momordok']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['nopo']."</th>	
				<th align=center>".$_SESSION['lang']['supplier']."</th> 
				<th align=center>".$_SESSION['lang']['asaltujuan']."</th>
				<th align=center>".$_SESSION['lang']['noreferensi']."</th>			  
				<th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
				<th align=center width=50px>Action</th>
			</tr>
			</thead>
			<tbody>";
			
		$where = "";
		if($_SESSION['empl']['subbagian']!=""){	
			$str = "select * from ".$dbname.".kebun_5gudangtransaksi where 1=1 and afdeling ='".$_SESSION['empl']['subbagian']."' and status='1'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$gudangdiv[$bar['kodegudang']]=$bar['kodegudang'];
			}
			
			$where.=" and kodegudang in ('".implode("','",$gudangdiv)."')";
		}else if($param['gudang']!=''){
			$where.=" and kodegudang = '".$param['gudang']."'";
		}
			
		if($param['pt']!=''){
			$where.=" and kodept = '".$param['pt']."'";
		}
		if($param['nodok']!=''){
			$where.=" and notransaksi like '%".$param['nodok']."%'";
		}
		if($param['nopo']!=''){
			$where.=" and nopo like '%".$param['nopo']."%'";
		}
		if($param['asal']!=''){
			$where.=" and gudangx = '".$param['asal']."'";
		}
		if($param['noref']!=''){
			$where.=" and notransaksireferensi like '%".$param['noref']."%'";
		}
		if($param['tanggal']!=''){
			$where.=" and tanggal = '".tanggalsystemn($param['tanggal'])."'";
		}
		if($param['tipe']!=''){
			$where.=" and tipetransaksi = '".$param['tipe']."'";
		}
		
		$limit= 10;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   =12;
		
		$sql = "select count(*) as jmlhrow from ".$dbname.".log_transaksiht where substr(kodegudang,1,4) in ('".implode("','",getdetorglog())."') ".$whtrans." ".$where."";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		$cekadmin = makeOption($dbname,'admin_list','username,username',"username='".$_SESSION['standard']['username']."'");
		
		$str="select * from ".$dbname.".log_transaksiht where substr(kodegudang,1,4) in ('".implode("','",getdetorglog())."') ".$whtrans." ".$where." order by kodept asc, tanggal asc, tipetransaksi asc, notransaksi asc limit " . $offset . "," . $limit . "";
		$res=fetchdata($str);
		if(count($res) > 0){
			foreach($res as $key=>$val){
				$no++;
				
				$optklbrg= makeOption($dbname,'log_5klbarang','kode,kelompok',"kode='".$val['kelompokbarang']."'");
				$nmsup   = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['idsupplier']."'");
				$nmgdg   = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodegudang']."' or kodeorganisasi='".$val['kodept']."' or kodeorganisasi='".$val['gudangx']."'");
				
				$tab.="<tr class='rowcontent' style=height:20px id=row_".$no." name=baris[]>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td>".$nmgdg[$val['kodept']]."</td>";
				$tab.="<td>".$nmgdg[$val['kodegudang']]."</td>";
				$tab.="<td>(".$val['tipetransaksi'].") ".getDetailTipeMutasi($val['tipetransaksi'])."</td>";
				$tab.="<td id=notran".$no.">".$val['notransaksi']."</td>";
				$tab.="<td style='min-width:60px;text-align:center'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td>".($val['nopo']=='-'?'':$val['nopo'])."</td>";
				$tab.="<td>".$nmsup[$val['idsupplier']]."</td>";
				$tab.="<td>".$nmgdg[$val['gudangx']]."</td>";
				$tab.="<td>".$val['notransaksireferensi']."</td>";
				$tab.="<td style='text-align:center'>".getNamaKaryawan($val['user'])."</td>";
				
				$tab.="<td style='text-align:center'>
					<button onclick=\"showposting('".$val['notransaksi']."','".$no."')\" class=mybutton>".$_SESSION['lang']['proses']."</button>
				</td>";
				$tab.="</tr>";
			}
			if($cekadmin[$_SESSION['standard']['username']]!=''){				
				$tab.="<tr class='rowcontent' style=background-color:#71FFFC;>";
				$tab.="<td style='text-align:center' colspan='".($colspan-1)."'></td>";
				$tab.="<td style='text-align:center'>
						<button style=border-color:red; id=btnpostall onclick=\"postingall('".$no."')\" class=mybutton>PostAll</button></td>";
				$tab.="</tr>";
			}		
		}else{
			$tab.="<tr class='rowcontent'><td colspan='".$colspan."' style='text-align:center;height:50px;font-size:20px'>".$_SESSION['lang']['datanotfound'].", silahkan filter <b>Perusahaan</b> dan <b>Gudang</b> jika masih kosong berarti data sudah di posting seluruhnya.</td></tr>";
		}
			
		
		$tab.="</tbody>";
		$tab.="<tfoot>";
		$tab.=createpaginglog($jlhbrs,$limit,$page,$colspan,'listposting','getPagePost');
		$tab.="</tfoot>";
		$tab.="</table>";
		
		
        
		echo $tab;
	break;
	case'showposting':
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader style=height:25px>
             <th align=center>No</th>
             <th align=center>".$_SESSION['lang']['kodebarang']."</th>
             <th align=center>".$_SESSION['lang']['namabarang']."</th>
             <th align=center>".$_SESSION['lang']['satuan']."</th>
             <th align=center>".$_SESSION['lang']['kuantitas']."</th>
             <th align=center>".$_SESSION['lang']['alokasi']."</th>
		</tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".log_transaksidt where notransaksi='".$param['notransaksi']."'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nmbrg= makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['kodebarang'] . "</td>";
            $tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
            $tab.="<td align=center>".$bar['satuan']."</td>";
            $tab.="<td align=right>" . @number_format($bar['jumlah'], 2) . "</td>";
            $tab.="<td align=center>" . ($bar['kodemesin']!=''?$bar['kodemesin']:$bar['kodeblok']) . "</td>";
        }
        $tab.="</tr>";
		
		$tab.="<tr class=rowcontent style=height:25px>";
		$tab.="<td align=center colspan='6'>
				<button onclick=\"posting('".$param['notransaksi']."')\" class=mybutton>".$_SESSION['lang']['posting']."</button>
				<button onclick=\"batalpost()\" class=mybutton>".$_SESSION['lang']['cancel']."</button>
				</td>";
        $tab.="</tr>";
		
        $tab.="</table>";
        echo $tab;
	break;
	
    case'loaddata':
        $where = "";
		if($param['pt']!=''){
			$where.=" and kodept = '".$param['pt']."'";
		}
		if($param['gudang']!=''){
			$where.=" and kodegudang = '".$param['gudang']."'";
		}
		if($param['nodok']!=''){
			$where.=" and notransaksi like '%".$param['nodok']."%'";
		}
		if($param['nopo']!=''){
			$where.=" and nopo like '%".$param['nopo']."%'";
		}
		if($param['asal']!=''){
			$where.=" and gudangx = '".$param['asal']."'";
		}
		if($param['noref']!=''){
			$where.=" and notransaksireferensi like '%".$param['noref']."%'";
		}
		if($param['tanggal']!=''){
			$where.=" and tanggal = '".tanggalsystemn($param['tanggal'])."'";
		}
		if($param['tipe']!=''){
			$where.=" and tipetransaksi = '".$param['tipe']."'";
		}
		
        $tab = "";
		
		$limit= 10;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   =13;
		
        $sql = "select count(*) as jmlhrow from ".$dbname.".log_transaksiht where substr(kodegudang,1,4) in ('".implode("','",getdetorglog())."') and post='1' ".$where."";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['jmlhrow'];
		
		
		$str="select * from ".$dbname.".log_transaksiht where substr(kodegudang,1,4) in ('".implode("','",getdetorglog())."') and post='1' ".$where." order by kodept asc, tanggal desc, tipetransaksi asc limit " . $offset . "," . $limit . "";
		$res=fetchdata($str);
		if(count($res) > 0){
			foreach($res as $key=>$val){
				$no++;
				
				$optklbrg= makeOption($dbname,'log_5klbarang','kode,kelompok',"kode='".$val['kelompokbarang']."'");
				$nmsup   = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['idsupplier']."'");
				$nmgdg   = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodegudang']."' or kodeorganisasi='".$val['kodept']."' or kodeorganisasi='".$val['gudangx']."'");
				
				$tab.="<tr class='rowcontent' style=height:20px>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td>".$nmgdg[$val['kodept']]."</td>";
				$tab.="<td>".$nmgdg[$val['kodegudang']]."</td>";
				$tab.="<td>(".$val['tipetransaksi'].") ".getDetailTipeMutasi($val['tipetransaksi'])."</td>";
				$tab.="<td>".$val['notransaksi']."</td>";
				$tab.="<td style='min-width:60px;text-align:center'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td>".($val['nopo']=='-'?'':$val['nopo'])."</td>";
				$tab.="<td>".$nmsup[$val['idsupplier']]."</td>";
				$tab.="<td>".$nmgdg[$val['gudangx']]."</td>";
				$tab.="<td>".$val['notransaksireferensi']."</td>";
				$tab.="<td style='text-align:center'>".getNamaKaryawan($val['user'])."</td>";
				$tab.="<td style='text-align:center'>".getNamaKaryawan($val['postedby'])."</td>";
				$tab.="<td align=center width=25px><img src=images/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' 
                    onclick=\"previewDocument(".$val['tipetransaksi'].",'" . $val['notransaksi'] . "','event');\" ></td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</tr>";
		}
		
		## PAGING
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
        break;
}

function getdetorglog($username=''){
	global $dbname;
	global $owlPDO;
	
	if($username==''){
		$username=$_SESSION['standard']['username'];
	}
	
	
	$str="select distinct(a.kodeorganisasi) as kodeorganisasi, b.namaorganisasi, b.alokasi from ".$dbname.".user_orgdetail a left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi where length(b.kodeorganisasi)=4 and a.namauser='".$username."' order by b.kodeorganisasi";
	$res = fetchdata($str);
	foreach ($res as $bar) {
		//$hasil[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	$hasil[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
	
	return $hasil;
}

function createpaginglog($jlhbrs,$limit,$page,$colspan,$loaddata,$getpage){
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$totrows=ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	
	$isiRow='';
	for($er=1;$er<=$totrows;$er++){
		$sel = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	
	$frompage = (($page*$limit)+1);
	if((($page+1)*$limit) > $jlhbrs){
		$topage = $jlhbrs;
	}else{
		$topage = (($page+1)*$limit);
	}
	$tab.="<tfoot><tr>
		<td colspan=".$colspan." align=center>
			".$frompage." to ".$topage." Of ".  $jlhbrs."
		</td>
	</tr>
	<tr>
		<td colspan=".$colspan." align=center>";
			if($page=='0'){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=$loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}
			$tab.="<select id=\"pageslog\" name=\"pageslog\" style=\"min-width:20px\" onchange=\"$getpage()\">".$isiRow."</select>";
			
			if(($page+1) == $totrows){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=$loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
		$tab.="</td>
	</tr>
	</tfoot>";
	
	return $tab;
}
?>	
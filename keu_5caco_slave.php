<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$method         = checkPostGet('method', '');
$kd             = checkPostGet('kd', '');
$kdtujuan       = checkPostGet('kdtujuan', '');
$kode           = checkPostGet('kode', '');
$kodesch        = checkPostGet('kodesch','');
$kodetujuan   	= checkPostGet('kodetujuan', '');
$kodetujuansch 	= checkPostGet('kodetujuansch','');
$akunpiutang   	= checkPostGet('akunpiutang','');
$akunhutang     = checkPostGet('akunhutang','');
$pagejs         = checkPostGet('page','');
$unit         = checkPostGet('unit','');



$kodeorg   	= checkPostGet('kodeorg', '');
$kodeorgtujuan   	= checkPostGet('kodeorgtujuan', '');
$akunpiutang   	= checkPostGet('akunpiutang','');
$akunhutang     = checkPostGet('akunhutang','');
$jenis     = checkPostGet('jenis','');


$stream="";

switch ($method) {
   
   
	case'input':
		// exit("Error".$kodeorg._.$jenis);
		
		#= ambil data yang sudah ada
		$str  = "SELECT * FROM  ".$dbname.".keu_5caco WHERE kodeorg='".$kodeorg."' and jenis='".$jenis."'";
		$res  = fetchdata($str);
		foreach ($res as $bar){
			$dataakunhutang[$bar['kodeorgtujuan']]=$bar['akunhutang'];
			$dataakunpiutang[$bar['kodeorgtujuan']]=$bar['akunpiutang'];
		}
		
		
		// echo"<pre>";
		// print_r($dataakunhutang);
		// echo"</pre>";
		
			// echo"<pre>";
		// print_r($dataakunpiutang);
		// echo"</pre>";
		
		$optakunhutang=$optakunpiutang=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		
		
		$str  = "SELECT * FROM  ".$dbname.".organisasi WHERE length(kodeorganisasi)=4";
		$res  = fetchdata($str);
		foreach ($res as $bar){
			if($kodeorg==$bar['kodeorganisasi']){
				$optunit="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
			// $optunittujuan.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		
		
		
		$arrjenis=array("kasbank"=>"Kas/Bank","barang"=>"Barang/Jasa","lainnya"=>"Lainnya");
		$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		// exit("Error:$jenis");
		foreach($arrjenis as $idjenis => $listjenis){
			if($idjenis==$jenis){
				$optjenis.="<option value='".$idjenis."' selected>".$listjenis."</option>";
			}else{
				$optjenis.="<option value='".$idjenis."'>".$listjenis."</option>";
			}
			
		}
		
		
		
		OPEN_BOX();

			$stream.="<fieldset style='float:left; widht:auto;'>";
				$stream.="<legend>" . $_SESSION['lang']['awal'] . "</b></legend>"; 
					$stream.="<table>";
					$stream.="<tr>";
						$stream.="<td align=left>".$_SESSION['lang']['unit']."</td>";
						$stream.="<td>:</td>";
						$stream.="<td><select id=kodeorg  style='width:300px;'>".$optunit."</select></td>";
					$stream.="</tr>";	
					$stream.="<tr>";
						$stream.="<td align=left>".$_SESSION['lang']['jenis']."</td>";
						$stream.="<td>:</td>";
						$stream.="<td><select id=jenis onchange=fillfield('".$kodeorg."'); style='width:300px;'>".$optjenis."</select></td>";
					$stream.="</tr>";	
					
				$stream.="</table>";
			$stream.="</fieldset>";	
			
			$stream.="<div style=clear:both></div>";	
			$no=0;
			$stream.="<fieldset style='float:left; widht:auto;'>";
				$stream.="<legend>" . $_SESSION['lang']['tujuan'] . "</b></legend>"; 
					$stream.="<table class=sortable cellspacing=1 border=0>";
						$stream.="<thead>";
						$stream.="<tr class=rowheader>";
									$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
									$stream.="<td align=center>".$_SESSION['lang']['unit']."</td>";
									$stream.="<td align=center>".$_SESSION['lang']['akunhutang']."</td>";
									$stream.="<td align=center>".$_SESSION['lang']['akunpiutang']."</td>";
						$stream.="</tr>";	
						$stream.="</thead>";						
						foreach ($res as $bar){
							if($kodeorg!=$bar['kodeorganisasi']){
								$no++;
								
								$optakunhutang=$optakunpiutang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
								$strakun	="SELECT * FROM ".$dbname.".keu_5akun where 
									(noakun like '114%' or noakun like '121%' or noakun like '215%' or noakun like '222%') and detail=1 order by noakun";
								$resakun  = fetchdata($strakun);
								foreach ($resakun as $barakun){
									if($dataakunhutang[$bar['kodeorganisasi']]==$barakun['noakun']){
										$optakunhutang.="<option value='".$barakun['noakun']."' selected>".$barakun['noakun']." - ".$barakun['namaakun']."</option>";
									}else{
										$optakunhutang.="<option value='".$barakun['noakun']."'>".$barakun['noakun']." - ".$barakun['namaakun']."</option>";
									}
									
									if($dataakunpiutang[$bar['kodeorganisasi']]==$barakun['noakun']){
										$optakunpiutang.="<option value='".$barakun['noakun']."' selected>".$barakun['noakun']." - ".$barakun['namaakun']."</option>";
									}else{
										$optakunpiutang.="<option value='".$barakun['noakun']."'>".$barakun['noakun']." - ".$barakun['namaakun']."</option>";
									}
								}
								
								
								$stream.="<tr class=rowcontent id=row".$no.">";
										$stream.="<td align=center>".$no."</td>";
										$optunittujuan="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
										$stream.="<td><select id=kodeorgtujuan".$no."  value='".$bar['kodeorganisasi']."' style='width:300px;'>".$optunittujuan."</select></td>";
										$stream.="<td><select id=akunhutang".$no." style='width:300px;'>".$optakunhutang."</select></td>";
										$stream.="<td><select id=akunpiutang".$no." style='width:300px;'>".$optakunpiutang."</select></td>";
								$stream.="</tr>";	
							}
						}
						$stream.="<tr class=rowcontent>";
							$stream.="<td align=center colspan=4><button  id=save class=mybutton colspan=3 onclick=savedata(".$no.")>".$_SESSION['lang']['save']."</button>";
						$stream.="</tr>";	
					$stream.="</table>";
			$stream.="</fieldset>";
			
			
			echo $stream;
		CLOSE_BOX();
	break;
	
	
	
	
	case'savedata':
	
	
		if($jenis==''){
			exit("Error:Jenis Masih Kosong");
		}
	
		#= delete 1st
		$str     = "delete from " . $dbname . ".keu_5caco where 
			kodeorg='".$kodeorg."' and kodeorgtujuan='".$kodeorgtujuan."' and jenis='".$jenis."' ";
		try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	
	
		if($param['akunhutang'] != '' && $param['akunpiutang'] != '') {
			#= insert
			$str     = "INSERT INTO " . $dbname . ".keu_5caco 
			(`kodeorg`,`kodeorgtujuan`,`jenis`,`akunpiutang`,`akunhutang`,`createby`,`createtime`) VALUES 
			('" . $kodeorg . "','" . $kodeorgtujuan. "','" . $jenis. "','" . $akunpiutang . "','" . $akunhutang . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
	break;
   
   
   
    case'loaddata':
		$limit      = 20;
        $page       = 0;
        $colspan    = 4;

		if (isset($pagejs)) {
			$page   = $pagejs;
			if ($page < 0)
				$page = 0;
        }
        $offset     = floatval($page) * $limit;
		$maxdisplay = (floatval($page) * $limit);
        $no         = ((floatval($page) * $limit));
        
		$where='';
        if($kodeorg!=''){ 
            $where.=" and kodeorganisasi='".$kodeorg."'";
        }

        $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".organisasi WHERE length(kodeorganisasi)=4 ".$where."";
        $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs     = owlBaris($res);
		$res        = fetchdata($str);
		$jlhbrs     = $res[0]['jmlhrow'];
        $totrows    = ceil($jlhbrs / $limit);
        
        if($totrows == 0){
            $totrows = 1;
        }
                
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++){
            $sel    = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        
        $frompage = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }

      
            $str  = "SELECT * FROM  ".$dbname.".organisasi WHERE length(kodeorganisasi)=4  ".$where." LIMIT ".$offset.",".$limit." ";
            $res  = fetchdata($str);
            foreach ($res as $bar){
                $optOrg         = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
                $optOrgtujuan   = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorgtujuan']."'");
                $optpiutang     = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$bar['akunpiutang']."'");
                $opthutang      = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$bar['akunhutang']."'");
                $no+=1;
                echo   "<tr class=rowcontent>";
                    echo   "<td align=center>" . $no . "</td>";
                    echo   "<td align=left>".$bar['kodeorganisasi']."</td>";
                    echo   "<td align=left>".$bar['namaorganisasi']."</td>";
                    echo   "<td align=center>
                                <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillfield('" . $bar['kodeorganisasi'] . "');\">
                            </td>";
                echo   "</tr>
                    </tbody>";
            }
            echo   "<tfoot>
                        <tr>
                            <td colspan=".$colspan." align=center>
                                ".$frompage." to ".$topage." Of ".  $jlhbrs."
                            </td>
                        </tr>";
                echo   "<tr>
                            <td colspan=".$colspan." align=center>";
                            if($page!=0){
                                echo  "<button class=mybutton onclick=loaddata(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                            }
                            echo" <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                            if((floatval($page)+1) != $totrows){
                                echo "<button class=mybutton onclick=loaddata(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                            }
                echo        "</td>
                        </tr>
                    </tfoot>";
        
    break;

    case 'insert':
        $str    = "SELECT * FROM " . $dbname . ".keu_5caco WHERE kodeorg ='".$kode."' AND kodeorgtujuan ='".$kodetujuan."'";
        $res    = fetchData($str);
        if(count($res)>0){exit("Warning : Data sudah ada !");}
        $ha     = "INSERT INTO " . $dbname . ".keu_5caco 
                    (`kodeorg`,`kodeorgtujuan`,`akunpiutang`,`akunhutang`,`createby`,`createtime`) VALUES 
                    ('" . $kode . "','" . $kodetujuan. "','" . $akunpiutang . "','" . $akunhutang . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
        try{
            $owlPDO->exec($ha);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'update':
        $ha     = "UPDATE " . $dbname . ".keu_5caco SET 
                    akunpiutang='" . $akunpiutang . "',
                    akunhutang='" . $akunhutang . "',
                    updateby='" . $_SESSION['standard']['userid'] . "' WHERE 
                    kodeorg='" . $kode . "' AND 
                    kodeorgtujuan='" . $kodetujuan . "'";
        try{
            $owlPDO->exec($ha);
        }catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'delete':
        $ha     = "DELETE FROM " . $dbname . ".keu_5caco WHERE kodeorg='" . $kode . "' AND kodeorgtujuan='".$kdtujuan."'";
        try {
            $owlPDO->exec($ha);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    default:
}
?>
<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');
 
    $pagejs         = checkPostGet('page', '');
    $method         = checkPostGet('method', '');
    $idpremi        = checkPostGet('idpremi', '');
    $kodeorg        = checkPostGet('kodeorg', '');
    $tanggal        = checkPostGet('tanggal', '');
    $dari           = checkPostGet('dari', '');
    $sampai         = checkPostGet('sampai', '');
    $harga          = checkPostGet('harga', '');
?>

<?php
switch ($method) {
 
    case 'simpan':
        $ht = "INSERT INTO ".$dbname. ".kebun_5basispanen 
                    (`kodeorg`,`tanggalberlaku`,`batasbawah`,`batasatas`,`harga`) VALUES 
                    ('" . $kodeorg . "','" . tanggalsystemn($tanggal). "','" . $dari . "','" . $sampai . "','" . $harga . "')";
        try{
            $owlPDO->exec($ht);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
  
    case'loadData':
        $tab        ="";
        $footer     ="";
		$limit      = 10;
        $page       = 0;
        $colspan    = 13;

		if (isset($pagejs)) {
			$page   = $pagejs;
			if ($page < 0)
				$page = 0;
        }
        
		$offset     = floatval($page) * $limit;
		$maxdisplay =(floatval($page) * $limit);
        $no         =((floatval($page) * $limit));
        
    
        $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname. ".kebun_5basispanen";
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
        
        $frompage   = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }

        if($jlhbrs < 1){
                $tab.="<tr class=rowcontent>
                            <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                        </tr>";
        }else{
            $iList  = "SELECT * FROM " . $dbname . ".kebun_5basispanen  LIMIT ".$offset.",".$limit." ";
            $hasil  = fetchdata($iList);
            
            foreach ($hasil as $dList){
               
                $no+=1;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>" . $no . "</td>";
                    $tab.="<td align=left>".$dList['kodeorg']."</td>";
                    $tab.="<td align=left>".tanggalnormal($dList['tanggalberlaku'])."</td>";
                    $tab.="<td align=right>".number_format($dList['batasbawah']) . "</td>";
                    $tab.="<td align=right>".number_format($dList['batasatas'])."</td>";
                    $tab.="<td align=right>".number_format($dList['harga']) . "</td>";
                   
                    $tab.="<td align=center>
                                    <img src=images/application/application_edit.png class=zImgBtn  tittle='Edit'  onclick=\"fillField('" . $dList['id'] . "','" . $dList['kodeorg'] . "','" . tanggalnormal($dList['tanggalberlaku']) . "','" . $dList['batasbawah'] . "','" . $dList['batasatas'] . "','" .$dList['harga'] . "');\">
                                    </td>";
                    $tab.="<td align=center>            
                                    <img src=images/application/application_delete.png class=zImgBtn  tittle='Delete'  onclick=\"del('" . $dList['id'] . "');\">
                                    </td>";
                         
                $tab.="</tr>
             </tbody>";  
            }
                $footer .= "<tr>
                                <td colspan=".$colspan." align=center>
                                    ".$frompage." to ".$topage." Of ".  $jlhbrs."
                                </td>
                            </tr>";
                $footer .= "<tr>
                                <td colspan=".$colspan." align=center>";
                                if($page!=0){
                                    $footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                                }
                $footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                                if((floatval($page)+1) != $totrows){
                                    $footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                                }
                $footer .=     "</td>
                            </tr>";
        }
        echo $tab."####".$footer;
    break;
  
    case 'update':
        $ht     = "UPDATE " . $dbname . ".kebun_5basispanen SET 
                    kodeorg='" . $kodeorg . "',
                    tanggalberlaku='" . tanggalsystemn($tanggal) . "',
                    batasbawah='" . $dari . "',
                    batasatas='" . $sampai . "',
                    harga='" . $harga . "'

                    WHERE id='" . $idpremi . "' ";
        try{
            $owlPDO->exec($ht);
        }catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'delete':
        $ht     = "DELETE FROM " . $dbname . ".kebun_5basispanen WHERE id='".$idpremi."' ";
        try {
            $owlPDO->exec($ht);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    default:
}
?>
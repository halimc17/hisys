<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$pt = checkPostGet('pt','');
$periode = checkPostGet('periode','');
$kodebarang = checkPostGet('kodebarang','');
$qty = checkPostGet('qty','');
$harga = checkPostGet('harga','');
$method = checkPostGet('method','');

$whrbrg="inactive='0' and kelompokbarang='400' ";
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whrbrg);

$whrorg="tipe='PT'";
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg);

switch($method){
	case 'insert':
		if ($periode == '' || $pt == '' || $kodebarang == '') {
			exit("Warning:Lengkapi Pengisian");
		}
	
		#= delete
		$str="delete from ".$dbname.".pmn_estimasipenerimaan where 
			pt='".$pt."' and periode='".$periode."' and kodebarang='".$kodebarang."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
		
			
		#= insert
		$str="insert into ".$dbname.".pmn_estimasipenerimaan (pt,periode,kodebarang,
				qty,harga,createdby,createtime)
			  values('".$pt."','".$periode."','".$kodebarang."','".$qty."','".$harga."','".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;

    case'deldt':

        $strdt = "delete from ".$dbname.".pmn_estimasipenerimaan where periode='".$periode."' and kodebarang='".$kodebarang."' and pt='".$pt."' ";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'posting':
        $strnd="update ".$dbname.".pmn_estimasipenerimaan set posting='1',postingby='".$_SESSION['standard']['userid']."' where periode='".$periode."' and kodebarang='".$kodebarang."' and pt='".$pt."'";             
        try
        {
            $owlPDO->exec($strnd);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

	case 'loadData':
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".pmn_estimasipenerimaan ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=9>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".pmn_estimasipenerimaan order by periode desc,pt asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
               
                @$no+=1;
                $tab.="<tr class=rowcontent>
				<td>".$bar['periode']."</td>
                    <td>".$nmorg[$bar['pt']]."</td>
					 <td>".$nmbrg[$bar['kodebarang']]."</td>
					  <td align=right>".number_format($bar['qty'])."</td>
					  <td align=right>".number_format($bar['harga'])."</td>
					  <td align=right>".number_format($bar['harga']*$bar['qty'])."</td>";

                    if ($bar['posting']==0){
                    $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['periode']."','".$bar['pt']."','".$bar['kodebarang']."','".$bar['qty']."','".$bar['harga']."')\"></td>
                           <td><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"deldt('".$bar['periode']."','".$bar['pt']."','".$bar['kodebarang']."');\" ></td>
                           <td><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"posting('".$bar['periode']."','".$bar['pt']."','".$bar['kodebarang']."');\" ></td>";
                    }else{
                        $tab.="<td align=center colspan=3><img src=images/skyblue/posted.png class=resicon  title='Posted' ></td>";   
                    }
                $tab.="</tr>";

            }
            $totrows=ceil($jlhbrs/$limit);
            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo @$tab."####".@$footd;
    break;

	default:
	   break;					
}


?>
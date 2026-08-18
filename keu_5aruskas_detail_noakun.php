<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
$frm = array('','','','','');
$user_entry=$_SESSION['standard']['userid'];
$nomorarus = false;
if(isset($_POST['noakun_detail'])){
	$nomorarus = $_POST['noakun_detail'];
}
$prosses = '';
if(isset($_POST['prosses'])){
	$prosses = $_POST['prosses'];
}

if($nomorarus == false){
	exit();
}

$str="select * from ".$dbname.".keu_5aruskas where noaruskas='".$nomorarus."'";
$resv=fetchData($str);
$optakunht="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($resv as $bar => $barv){
	$namaaruskas=$barv['nama_aruskas'];	
	$optakunht.="<option value='".$barv['noaruskas']."'>".$barv['nama_aruskas']."</option>";
 }

### Get Value No AKun '126%'and '611%' and '621%' and '63%' gua buka biar bisa dinput pas boronan
// $str=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun where length(noakun)=7 order by namaakun");

#= ind rev 25 nov => kunci jurnalmemorial=1 karna akun alokasi dan akun persediaan tidak muncul dijurnal memo.
$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".keu_5akun where jurnalmemorial=1";
$res=fetchData($str);
foreach($res as $bar){
	$optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
 }

#noakun not like '126%' 
#and noakun not like '611%' and noakun not like '621%' and noakun not like '63%' 
#and

/*$str = "select count(*) as jumlah from " . $dbname . ".keu_5akun where char_length(noakun)=7 and noakun not in 
(select noakun from " . $dbname . ".keu_5aruskas_detail where noaruskas='".$nomorarus."')";*/
//noakun not like '115%' and 

/*
$str="select count(*) as jumlah from " . $dbname . ".keu_5akun where noakun not like '126%' 
and noakun not like '611%' and noakun not like '621%'
and length(noakun)=7 and noakun not in 
(select noakun from " . $dbname . ".keu_5aruskas_detail where noaruskas='".$nomorarus."')";
*/
$str="select count(*) as jumlah from " . $dbname . ".keu_5akun where  length(noakun)='7' and noakun not in 
(select noakun from " . $dbname . ".keu_5aruskas_detail where noaruskas='".$nomorarus."')";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res ->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$jumlah=$bar['jumlah'];
		
		$frm[0]="
							<input id='methodNoakun' class='myinputtext' name='prosses' type='hidden' value='insertAkun'>
							<table style=display:none>

								<tr>
				                    <td>".$_SESSION['lang']['nomor']." ".$_SESSION['lang']['aruskas']."</td>
				                    <td>:</td>
				                    <td><input type=text id=nomorarus class=myinputtext style=width:150px; value='".$nomorarus."' disabled></td>
				                </tr>
  
			                	<tr><td></td>
			                        <td colspan=3>
										<button class=mybutton onclick=simpanNoakun(".$jumlah.")>" . $_SESSION['lang']['save'] . "</button>
										<button class=mybutton onclick=detailAkun('".$nomorarus."')>" . $_SESSION['lang']['cancel'] . "</button>
			                        </td>
			                	</tr>
							</table>";
					//$frm[0].="</fieldset><fieldset><legend>List</legend>";
					$frm[0].="<div id='contentBox' style='width=100%; max-height:320px;overflow:auto;'>";
					$frm[0].="<table class=sortable cellpadding=5 width=100% cellspacing=1 border=0  >
					        <thead>
					       		<tr class=rowheader>
							        <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
							        <th align=center>" . $_SESSION['lang']['noakun']."</th>
							        <th align=center>" .$_SESSION['lang']['akun']. "</th>
							        <th align=center>" . $_SESSION['lang']['action'] . "</th>
							    </tr>
					    	</thead>
					    	<tbody>";

					
					/*
					$str = "select * from " . $dbname . ".keu_5akun where noakun not like '126%' 
					and noakun not like '611%' and noakun not like '621%'
					and length(noakun)=7 and noakun not in 
					(select noakun from " . $dbname . ".keu_5aruskas_detail where noaruskas='".$nomorarus."')";
					*/
					//noakun not like '115%' and 
					$str="select *  from " . $dbname . ".keu_5akun where length(noakun)='7' and noakun not in 
					(select noakun from " . $dbname . ".keu_5aruskas_detail where noaruskas='".$nomorarus."')";
					$res=fetchdata($str);
					foreach($res as $bar){

		            $no+=1;
          
			            $frm[0].="<tr class=rowcontent id=row".$no.">";
			            $frm[0].="<td align=center>".$no."</td>";
			            $frm[0].="<td align=center id='noakundt_".$no."'>".$bar['noakun']."</td>";
			            $frm[0].="<td align=left>".$bar['namaakun']."</td>";
			            $frm[0].="<td align=center>
			                  		<input type=checkbox id='checkakun_".$no."'>";
			            $frm[0].="</tr>"; 
			        }
					
					$frm[0].="<tr class=rowcontent>
						<td colspan=4 align=center>
							<button class=mybutton onclick=simpanNoakunx(".$jumlah.")>" . $_SESSION['lang']['save'] . "</button>
							<button class=mybutton onclick=detailAkun('".$nomorarus."')>" . $_SESSION['lang']['cancel'] . "</button>
						</td>
					</tr>";
			       	$frm[0].="<input type=hidden id=totrow value=".$no.">";
			        $frm[0].="</tbody></table>";
					// $frm[0].="</form>	
					// </fieldset>
					// </div>
				// </div>
			// </div>
			// <div class=x-box-bl>
				// <div class=x-box-br>
					// <div class=x-box-bc></div>
				// </div>
			// </div>
	        // </div>";

		$frm[1].="
							
							<div style='width=100%; height:320px;overflow:auto;'>	
								<input type=hidden id=methodNoakun value='insertAkun'>
							<table>
						        <div id=containerNoakun> 
						            <script>loadDataAkun(".$nomorarus.")</script>
						        </div>
						    </table>
							";

// <script>loadDataAkun(".$nomorarus.")</script>

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,600);
?>

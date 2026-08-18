<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
$frm = array('','','','','');
$user_entry=$_SESSION['standard']['userid'];
$noinduk = false;
if(isset($_POST['noarus_detail'])){
	$noinduk = $_POST['noarus_detail'];
}
$prosses = '';
if(isset($_POST['prosses'])){
	$prosses = $_POST['prosses'];
}

if($noinduk == false){
	exit();
}

$strtipe = array ("M"=>"MASUK","K"=>"KELUAR");
$str="select * from ".$dbname.".keu_5aruskas where noaruskas='".$noinduk."'";
$resv=fetchData($str);
foreach($resv as $bar => $barv){
	$namaaruskas=$barv['nama_aruskas'];	
	//$optTipeTrans=$barv['tipetransaksi'];	
	$optTipeTrans.="<option value='" . $barv['tipetransaksi'] . "'>" . $barv['tipetransaksi'] . " - " . $strtipe[$barv['tipetransaksi']] . "</option>";
	$optPemilik.="<option value= '".@$barv['pemilik_aruskas']."'>".@$barv['pemilik_aruskas']. "</option>";
 }

		
		$frm[0].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; max-width:850px;'>
					<span class='judul'>DETAIL ARUS KAS</span><br>
					<fieldset>
						<legend>Form</legend>
						<input id='methodAkun' class='myinputtext' name='prosses' type='hidden' value='insertDetail'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
							<table>

								<tr>
				                    <td>".$_SESSION['lang']['nomor']." ".$_SESSION['lang']['induk']." ".$_SESSION['lang']['aruskas']."</td>
				                    <td>:</td>
				                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:60px;\" value='".$noinduk."' disabled>
									<input style=\"width:132px;\" disabled class=myinputtext value='".$namaaruskas."'></td>
				                    <td><input type=hidden  id=noinduk nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$noinduk."' disabled></td>
				                </tr>

				                <tr style='display:none'>
				                    <td >".$_SESSION['lang']['nomor']." Detail ".$_SESSION['lang']['aruskas']."</td> 
				                    <td>:</td>
				                    <td><input type=text placeholder='auto generate'  id=no_arus nkeypress=\"return_tanpa_kutip(event);\" disabled  class=myinputtext style=\"width:200px;\"></td>
				                </tr>

				                <tr>
				                    <td>".$_SESSION['lang']['nama']." detail ".$_SESSION['lang']['aruskas']."</td> 
				                    <td>:</td>
				                    <td><input type=text onkeydown=\"upperCaseF(this)\" id=nama_arus nkeypress=\"return_tanpa_kutip(event);\"  onkeypress='enterkey(event,simpandetail)'  class=myinputtext style=\"width:200px;\"></td>
				                </tr>

				                <tr>
			                    <td>".$_SESSION['lang']['tipetransaksi']."</td> 
			                    <td>:</td>
			                    <td><select id=tipe_trans disabled style=\"width:205px;\">".$optTipeTrans."</select></td>
			                </tr>
			                <tr>
			                    <td>".$_SESSION['lang']['pemilik']."</td> 
			                    <td>:</td>
			                    <td><select id=pemilik2 disabled style=\"width:205px;\">".$optPemilik."</select></td>
			                </tr>
			            
			                 <tr><td>".$_SESSION['lang']['status']."</td>
			                 <td>:</td>
			                 <td><input type=checkbox id=status2 checked>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>
			                

			                <tr><td colspan=2></td>
			                        <td colspan=3>
			                                <button class=mybutton onclick=simpandetail()>".$_SESSION['lang']['save']."</button>
			                                <button class=mybutton onclick=canceldetail()>".$_SESSION['lang']['cancel']."</button>
			                        </td>
			                </tr>

							</table>
						</form>	
					</fieldset>
					<div style='clear: both;'></div>
					
						<input type=hidden id=methodAkun value='insertDetail'>
					<table>
					<fieldset>
			        <legend>".$_SESSION['lang']['list']."</legend>
					<div style='width=100%; max-height:300px;overflow:auto; max-width:850px;'>	
			        <div id=containerAkundetail> 
			            <script>loadDataDetail(".$noinduk.")</script>
			        </div>
						</div>
			    </fieldset>
			    </table>
					
						</div>
					</div>
				</div>
				<div class=x-box-bl>
					<div class=x-box-br>
						<div class=x-box-bc></div>
					</div>
				</div>
	        </div>";


    /*=====Detail Form Rek Bank End =====*/


    

	
// $hfrm[0]="Daftar Rek Bank";

drawTab('FRM',$hfrm,$frm,200,650);
?>
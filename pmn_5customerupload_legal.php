<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
$frm = array('','','','','');
$user_entry=$_SESSION['standard']['userid'];
$kodecustomer = false;
if(isset($_POST['customer_detail'])){
	$kodecustomer = $_POST['customer_detail'];
}
$prosses = '';
if(isset($_POST['prosses'])){
	$prosses = $_POST['prosses'];
}

if($kodecustomer == false){
	exit();
}

// $strtipe = array ("M"=>"MASUK","K"=>"KELUAR");
// $str="select * from ".$dbname.".keu_5aruskas where noaruskas='".$noinduk."'";
// $resv=fetchData($str);
// foreach($resv as $bar => $barv){
// 	$namaaruskas=$barv['nama_aruskas'];	
// 	//$optTipeTrans=$barv['tipetransaksi'];	
// 	$optTipeTrans.="<option value='" . $barv['tipetransaksi'] . "'>" . $barv['tipetransaksi'] . " - " . $strtipe[$barv['tipetransaksi']] . "</option>";
// 	$optPemilik.="<option value= '".@$barv['pemilik_aruskas']."'>".@$barv['pemilik_aruskas']. "</option>";
//  }

 $nmsup=  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer',$whr);

		
		$tampilan="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; max-width:650px;'>
					<span class='judul'> FILE LEGAL PELANGGAN</span><br>

					<input type=hidden id=methoddt value='upload'>


                  <fieldset style='float:left'>
						<legend>Form</legend>
						
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
	
								<table cellspacing='1' border='0' id='uploadpopup'>
								<tr>
	                    <td>".$_SESSION['lang']['nmcust']."</td>
	                    <td>:</td>
	                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$kodecustomer]."' disabled></td>
	                    <td><input type=hidden  id=kodecustomer nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$kodecustomer."' disabled></td>
	                </tr>
								
							</table>

							<fieldset>

			<legend>".$_SESSION['lang']['list']."</legend>
			
				 <div id=containerAkundetail> 
		            <script>loadfiles(0)</script>
		        </div>
		    </fieldset>
                
                </table></fieldset>

		  
					</div>
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
echo $tampilan;
// drawTab('FRM',$hfrm,$frm,200,400);
?>
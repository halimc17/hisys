<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
// $frm = array('','','','','');
$user_entry=$_SESSION['standard']['userid'];
$notransaksi = false;
if(isset($_POST['transaksi_detail'])){
	$notransaksi = $_POST['transaksi_detail'];
}
$prosses = '';
if(isset($_POST['prosses'])){
	$prosses = $_POST['prosses'];
}

if($notransaksi == false){
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

 // $whr="notransaksi='".$notransaksi."'";
 // $nmsup=  makeOption($dbname, 'log_transaksiht', 'notransaksi',$whr);

		
		$tampilan="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; max-width:650px;'>";
		$tampilan="
					<input type=hidden id=methoddt value='upload'>
                  
						
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
	
								<table cellspacing='1' border='0' id='uploadpopup'>
								<tr>
	                    <td>".$_SESSION['lang']['nodok']."</td>
	                    <td>:</td>
	                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$notransaksi."' disabled></td>
	                    <td><input type=hidden  id=notransaksi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$notransaksi."' disabled></td>
	                </tr>
								
							</table>

							
			<div style=clear:both></div>
			
				<table class='sortable' cellpadding=5 cellspacing='1' border='0' width=100%>
					<thead>
					<tr class=rowheader>
					  <td align='center'>No.</td>

					  <td align='center'>Kriteria</td>
					  <td align='center'>Filename</td>
					  <td align='center'>Action</td>
					</tr>
					</thead>
					<tbody id=containerAkundetail><script>loadfiles(0)</script>
					</tbody>
				</table>
		    

                
                </table></fieldset>";

		  
					// </div>
					// </div>
				// </div>
			// </div>
			// <div class=x-box-bl>
				// <div class=x-box-br>
					// <div class=x-box-bc></div>
				// </div>
			// </div>
        // </div>";


    /*=====Detail Form Rek Bank End =====*/


    

	
// $hfrm[0]="Daftar Rek Bank";
echo $tampilan;
// drawTab('FRM',$hfrm,$frm,200,650);
?>
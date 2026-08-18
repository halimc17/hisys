<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
$user_entry=$_SESSION['standard']['userid'];
$id_supplier = false;
if(isset($_POST['idsupplier_detail'])){
	$id_supplier = $_POST['idsupplier_detail'];
}
$prosses = '';
if(isset($_POST['prosses'])){
	$prosses = $_POST['prosses'];
}
$id = '';
if(isset($_POST['id'])){
	$id = $_POST['id'];
}
$idlampiran = '';
if(isset($_POST['idlampiran'])){
	$idlampiran = $_POST['idlampiran'];
}
$badanusaha = '';
if(isset($_POST['badanusaha'])){
	$badanusaha = $_POST['badanusaha'];
}
$namasupplier = '';
if(isset($_POST['namasupplier'])){
	$namasupplier = $_POST['namasupplier'];
}
$legitimate = '';
if(isset($_POST['legitimate'])){
	$legitimate = $_POST['legitimate'];
}
$lokasifile = '';
if(isset($_POST['lokasifile'])){
	$lokasifile = $_POST['lokasifile'];
}
if($id_supplier == false){
	exit();
}
function str_less($s) {
    $c = array (' ');
    $d = array ('-','/','\\',',','#',':',';','\'','"','[',']','{','}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+');
    $s = str_replace($d, '', $s); // Hilangkan karakter yang telah disebutkan di array $d
    
    $s = strtolower(str_replace($c, '_', trim($s))); // Ganti spasi dengan tanda - dan ubah hurufnya menjadi kecil semua
    return $s;
}
//exit($prosses);
//exit("error : ".$badanusaha);
switch($prosses){
	default:
	$nmOrg1=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
	$whr="supplierid='".$id_supplier."'";
	$nmsup=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier',$whr);

$str2=$owlPDO->query("select supplierid,namasupplier from ".$dbname.".log_5supplier 
      order by namasupplier");
$str2->setFetchMode(PDO::FETCH_OBJ);
$optkeg='';
while($bar=$str2->fetch()){
    $optkeg.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}
// $str3=$owlPDO->query("select kode,kelompok from ".$dbname.".log_5klsupplier 
//       order by kelompok");
// $str3->setFetchMode(PDO::FETCH_OBJ);
// $optkode='';
// while($bar=$str3->fetch()){
//     $optkode.="<option value='".$bar->kode."'>".$bar->kelompok."</option>";
// }

### Get Value Enum Suppllier
// $optTipeSup = '';
// $arrTipeSup = getEnum($dbname, 'log_5klsupplier', 'tipe');
// foreach ($arrTipeSup as $kei => $fal) {
//     $optTipeSup.="<option value='" . $kei . "'>" . ucfirst(strtoupper($fal)) . "</option>";
// }

$str=$owlPDO->query("select tipe from ".$dbname.".log_5klsupplier
      order by tipe");
$str->setFetchMode(PDO::FETCH_OBJ);
$optTipeSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str->fetch()){
    $optTipeSup.="<option value='".$bar->tipe."'>".$bar->tipe."</option>";
}

// Get value no akun
// $str = "select noakun," . $zz . " from " . $dbname . ".keu_5akun where detail=1 and (noakun like '211%')";
$str4=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 and (noakun like '211%')
      order by namaakun");
$str4->setFetchMode(PDO::FETCH_OBJ);
$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str4->fetch()){
	$optakun.="<option value='".$bar->noakun."'>[".$bar->noakun."] - ".$bar->namaakun."</option>";
}

$optCurr = makeOption($dbname,'setup_matauang','kode,matauang');

$str1=$owlPDO->query("select kode,matauang from ".$dbname.".setup_matauang 
      order by matauang");
$str1->setFetchMode(PDO::FETCH_OBJ);
$optCurr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str1->fetch()){
    $optCurr.="<option value='".$bar->kode."'>".$bar->matauang."</option>";
}

// Get value pph
// $str=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun where namaakun like '%pph%'
//       order by namaakun");
// $str->setFetchMode(PDO::FETCH_OBJ);
// $optpph='';
// while($bar=$str->fetch()){
//     $optpph.="<option value='".$bar->noakun."'>".$bar->namaakun."</option>";
// }


// $optppn=array('PPN'=>'PPN');
// foreach ($optppn as $key) {
//  $optpph.="<option value= '".$key."'>".strtoupper($_SESSION['lang'][strtolower($key)]). "</option>";
// }
$res="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'zz' and kodeparameter = 'PPHSUP'";
$str=$owlPDO->query($res);
$str->setFetchMode(PDO::FETCH_OBJ);
$bar=$str->fetch();
$nilai=$bar->nilai;

$nilaiex=explode(',', $nilai);

foreach ($nilaiex as $key => $value) {
	$res="select noakun,namaakun from ".$dbname.".keu_5akun where noakun='".$value."' order by namaakun";
	$str=$owlPDO->query($res);
	$str->setFetchMode(PDO::FETCH_OBJ);

	while($bar=$str->fetch()){
	    // $optpph.="<option value='".$bar->noakun."'>".$bar->namaakun."</option>";
	    $optpph.="<option value='".$bar->noakun."'>[".$bar->noakun."] - ".$bar->namaakun."</option>";
	}
}


		
	/*=====Detail Form Rek Bank Start =====*/
	
	$sql = "SELECT * FROM ".$dbname.".keu_5daftarbank where status='1'";
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($data = $res->fetch()) {
		$optbank.="<option value=".$data['kodebank'].">".$data['namabank']."</option>";
	}

		$frm[0].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto;'>
					<span class='judul'>Data Rek. Bank</span><br>
					<fieldset style='float:left'>
						<legend>Form</legend>
						<input id='methodAkun' class='myinputtext' name='prosses' type='hidden' value='insert'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='".$id_supplier."'>
							<table>

								<tr>
				                    <td>".$_SESSION['lang']['supplier']."</td>
				                    <td>:</td>
				                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$id_supplier]."' disabled></td>
				                    <td hidden><input type=hidden  id=id_supplier nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
				                
				                    <td>".$_SESSION['lang']['namabank']."</td> 
				                    <td>:</td>
				                    <td><select id=bank style=\"width:205px;\" >".$optbank."</select></td>
				                </tr>

				                <tr>
				                    <td>".$_SESSION['lang']['norek']."</td> 
				                    <td>:</td>
				                    <td><input type=text  id=rekening nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
				              
				                    <td>".$_SESSION['lang']['atasnama']."</td> 
				                    <td>:</td>
				                    <td><input type=text  id=atasnama nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
				                </tr>

				                <tr>
				                    <td>".$_SESSION['lang']['cabang']."</td> 
				                    <td>:</td>
				                    <td><input type=text  id=cabang nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
				              
				                    <td>".$_SESSION['lang']['kota']."</td> 
				                    <td>:</td>
				                    <td><input type=text  id=kota nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
				                </tr>

				                <tr>
				                    <td>".$_SESSION['lang']['negara']."</td> 
				                    <td>:</td>
				                    <td><input type=text  id=negara nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
				               
				                    <td>".$_SESSION['lang']['matauang']."</td>
				                    <td>:</td>
				                    <td><select id=matauang style=\"width:205px;\">".$optCurr."</select></td>
				                </tr>

				                <tr><td>".$_SESSION['lang']['default']."</td>
				                 <td>:</td>
				                 <td><input type=checkbox id=def>".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."</td>
								 
								 <td>".$_SESSION['lang']['status']."</td>
				                 <td>:</td>
				                 <td><input type=checkbox id=statusbank>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>

								<tr><td colspan=2></td>
			                        <td colspan=3>
			                               <button class=mybutton onclick=saveAkun()>Simpan</button>
										<button class=mybutton onclick=cancelAkun()>Reset</button>
			                        </td>
			                </tr>
							</table>
						</form>	
					</fieldset>
					<div style='clear: both;'></div>
					
					<div style='width:100%;max-height:300px;overflow:auto;'>	
						<input type=hidden id=methodAkun value='insert'>
					<table>
					<fieldset style='float:left'>
			        <legend>".$_SESSION['lang']['list']."</legend>
			        <div id=containerAkun> 
			            <script>loadDataAkun(0)</script>
			        </div>
			    </fieldset>
			    </table>
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


    /*=====Detail Form NPWP Supplier Start =====*/

		$frm[1].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; '>
					<span class='judul'>Data NPWP Supplier</span><br>
					<fieldset style='float:left'>
						<legend>Form NPWP</legend>
				
							<input id='methoddt' class='myinputtext' name='prosses' type='hidden' value='insert'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
				<table>
					<tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$id_supplier]."' disabled></td>
                    <td hidden><input type=hidden  id=supplierid nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
                
                    <td>".$_SESSION['lang']['npwp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=npwp nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                
                    <td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['npwp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=namanpwp nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jalan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=jalan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                
                    <td>".$_SESSION['lang']['blok']."</td> 
                    <td>:</td>
                    <td><input type=text  id=blok nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                
                    <td>".$_SESSION['lang']['nomor']."</td> 
                    <td>:</td>
                    <td><input type=text  id=nomor nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['rt']."</td> 
                    <td>:</td>
                    <td><input type=text  id=rt nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['rw']."</td> 
                    <td>:</td>
                    <td><input type=text  id=rw nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['kelurahan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kelurahan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kecamatan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kecamatan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['kabupaten']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kabupaten nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['provinsi']."</td> 
                    <td>:</td>
                    <td><input type=text  id=propinsi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kodepos']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kodepos nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
              
                    <td>".$_SESSION['lang']['telp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=telp_no nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
              

                 <td>".$_SESSION['lang']['aktif']."</td>
                 <td>:</td>
                 <td><input type=checkbox id=aktif checked>".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."</td></tr>
                
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                                <button class=mybutton onclick=cancelTab()>Reset</button>
                        </td>
                </tr>
                </table></fieldset>
				
                    <input type=hidden id=methoddt value='upload'>
                  <fieldset style='float:left' max-width:150px;>
						<legend>Form Upload NPWP</legend>
						
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
	
								<table cellspacing='1' border='0' id='uploadpopup'>
								<tr>
	                    <td>".$_SESSION['lang']['supplier']."</td>
	                    <td>:</td>
	                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:230px;\" value='".$nmsup[$id_supplier]."' disabled></td>
	                    <td><input type=hidden  id=supplierid nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
	                </tr>
								<tr>
									<td>Filename</td>
									<td>:</td>
									<td>
										<input type='file' name='upload' id='upload' class=mybutton>
									</td>
								</tr>
								<tr>
									<td colspan=2></td>
									<td>
										<button class=mybutton onclick=\"submitfile()\">Submit</button>
									</td>
								</tr>
							</table>

							<fieldset>
			
			<legend>".$_SESSION['lang']['list']."</legend>
			
				<div id=containerUpload> 
		        </div>

			
		</fieldset>
                

                
                </table></fieldset>
<div style='clear: both;'></div>

				<fieldset>
		        <legend>".$_SESSION['lang']['list']."</legend>
		        <div id=container1 > 
		            <script>loadData(0)</script>
		        </div>
		    </fieldset>

		  
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
    /*=====Detail Form NPWP Supplier End =====*/


    /*=====Detail Form Kelompok Supplier Start =====*/

		/*$frm[2].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; '>
					<span class='judul'>Data Kelompok Supplier</span><br>
					<fieldset style='float:left'>
						<legend>Form Kelompok supplier</legend>
						<input id='methodKel' class='myinputtext' name='prosses' type='hidden' value='insert'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
				
							<table>
								<tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$id_supplier]."' disabled></td>
                    <td><input type=hidden  id=supplier_id nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipe']."</td> 
                    <td>:</td>
                    <td><select id=kode style=\"width:204px;\" onchange=getNoakunKl(0)>".$optTipeSup."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['noakun']."</td> 
                    <td>:</td>
                    <td><select id=noakun style=\"width:204px;\" disabled=disabled>".$optakun."</select></td>
                </tr>

                 <tr><td>".$_SESSION['lang']['status']."</td>
                 <td>:</td>
                 <td><input type=checkbox id='statkel'>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>
                

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpanKel()>Simpan</button>
                                <button class=mybutton onclick=cancelKel()>Reset</button>
                        </td>
                </tr>
                </table></fieldset>
                <div style='clear: both;'></div>

                <div style='width=100%; height:300px;overflow:auto; '>
                        <input type=hidden id=methodKel value='insert'>
				<table>
				<fieldset style='float:left'>
		        <legend>".$_SESSION['lang']['list']."</legend>
		        <div id=container2> 
		            <script>loadData2(0)</script>
		        </div>
		    </fieldset>
		    </table>
					</div>
					</div>
				</div>
			</div>
			<div class=x-box-bl>
				<div class=x-box-br>
					<div class=x-box-bc></div>
				</div>
			</div>
        </div>";*/
    /*=====Detail Form Kelompok Supplier End =====*/


    /*=====Detail Form Timbangan Supplier Start =====*/
	
		/*$frm[2].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; '>
					<span class='judul'>Data Timbangan Supplier</span><br>
					<fieldset style='float:left'>
						<legend>Form Timbangan Supplier</legend>
				<input id='methodTim' class='myinputtext' name='prosses' type='hidden' value='insert'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
							
							<table border=0 cellpadding=1 cellspacing=1>
								<tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$id_supplier]."' disabled></td>
                    <td><input type=hidden  id=supplierid1 nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kodetimbangan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kodetimbangan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" disabled></td>
                </tr>

                 <tr><td>".$_SESSION['lang']['status']."</td>
                 <td>:</td>
                 <td><input type=checkbox id=status1>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>
                

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpanSupTim()>Simpan</button>
                                <button class=mybutton onclick=cancelSupTim()>Reset</button>
                        </td>
                </tr>
                </table></fieldset>
                <div style='clear: both;'></div>
                <div style='width=100%; height:300px;overflow:auto; '>

                        <input type=hidden id=methodTim value='insert'>
				<table>
				<fieldset style='float:left'>
		        <legend>".$_SESSION['lang']['list']."</legend>
		        <div id=container3> 
		            <script>loadData3(0)</script>
		        </div>
		    </fieldset>
		    </table>
					</div>
					</div>
				</div>
			</div>
			<div class=x-box-bl>
				<div class=x-box-br>
					<div class=x-box-bc></div>
				</div>
			</div>
        </div>";*/
    /*=====Detail Form Timbangan Supplier End =====*/


    /*=====Detail Form Alamat Supplier =====*/
	
		$frm[2].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto;min-width:850px;'>
					<span class='judul'>Data Alamat Supplier</span><br>
					<fieldset style='float:left'>
						<legend>Form Alamat Supplier</legend>
				
							<input id='methodAlamat' class='myinputtext' name='prosses' type='hidden' value='insert'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
							<table>

						<tr>
                   
                    <td><input type=hidden  id=idalamat nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" disabled></td>
                </tr>
						<tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$id_supplier]."' disabled></td>
                    <td hidden><input type=hidden  id=supplierid2 nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
                
                    <td>".$_SESSION['lang']['alamat']."</td> 
                    <td>:</td>
                    <td><input type=text  id=alamatsup nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                
                    <td>".$_SESSION['lang']['kota']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kota1 nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>

                <tr>
                    <td>".$_SESSION['lang']['cperson']."</td> 
                    <td>:</td>
                    <td><input type=text  id=cperson nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['telp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=telp nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['extensi']."</td> 
                    <td>:</td>
                    <td><input type=text  id=extensi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>

                 <tr>
                    <td>".$_SESSION['lang']['nohp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=nohp nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
              
                    <td>".$_SESSION['lang']['jabatan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=jabatan1 nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
              
                    <td>".$_SESSION['lang']['fax']."</td> 
                    <td>:</td>
                    <td><input type=text  id=fax nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>

                <tr>
                    <td>".$_SESSION['lang']['email']." ".$_SESSION['lang']['koresponden']."</td> 
                    <td>:</td>
                    <td><input type=text  id=emailkor nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['email']." ".$_SESSION['lang']['konfirm']."</td> 
                    <td>:</td>
                    <td><input type=text  id=emailkon nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
               
                    <td>".$_SESSION['lang']['provinsi']."</td> 
                    <td>:</td>
                    <td><input type=text  id=provinsi1 nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>

                <tr>
                    <td>".$_SESSION['lang']['negara']."</td> 
                    <td>:</td>
                    <td><input type=text  id=negara1 nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
              
                    <td>".$_SESSION['lang']['kodepos']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kodepos1 onkeypress=\"return angka_doang(event);\"   class=myinputtextnumber	style=\"width:200px;\"></td>
                
				 <td>".$_SESSION['lang']['status']."</td>
                 <td>:</td>
                 <td><input type=checkbox id=statusalamat>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>
                
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpanAlamat()>Simpan</button>
                                <button class=mybutton onclick=cancelAlamat()>Reset</button>
                        </td>
                </tr>
                </table></fieldset>
                <div style='clear: both;'></div>
				<div >	

                        <input type=hidden id=methodAlamat value='insert'>
				<table>
				<fieldset style='float:left'>
		        <legend>".$_SESSION['lang']['list']."</legend>
		        <div id=containerAlamat style='width:100%;max-height:300px;overflow:auto;'> 
		            <script>loadDataAlamat(0)</script>
		        </div>
		    </fieldset>
		    </table>
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
    /*=====Detail Form ALAMAT NPWP End =====*/

    /*=====Detail Form pph start =====*/

    $frm[3].="<div id='' class='x-box-blue' style='clear: both;'>
			<div class='x-box-tl'>
				<div class='x-box-tr'>
					<div class='x-box-tc'></div>
				</div>
			</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto; '>
					<span class='judul'>Pajak</span><br>
					<fieldset style='float:left'>
						<legend>Pajak</legend>
				<input id='methodpph' class='myinputtext' name='prosses' type='hidden' value='insert'>
							<input id='idsupplier_detail' class='myinputtext' name='idsupplier_detail' type='hidden' value='<?php echo $id_supplier; ?>'>
							
							<table border=0 cellpadding=1 cellspacing=1>
								<tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type=text  nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$id_supplier]."' disabled></td>
                    <td><input type=hidden  id=supp_id nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$id_supplier."' disabled></td>
                </tr>

                 <tr>
                    <td>".$_SESSION['lang']['pajak']."</td> 
                    <td>:</td>
                    <td><select id=pph style=\"width:205px;\">".$optpph."</select></td>
                </tr>

                <tr>
                    <td>".$_SESSION['lang']['tarif']." %</td> 
                    <td>:</td>
                    <td><input type=text  id=tarif onkeypress=\"return angka_doang(event);\"   class=myinputtextnumber style=\"width:200px;\" ></td>
                </tr>

                 <tr><td>".$_SESSION['lang']['status']."</td>
                 <td>:</td>
                 <td><input type=checkbox id=statuspph>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpanpph()>Simpan</button>
                                <button class=mybutton onclick=cancelpph()>Reset</button>
                        </td>
                </tr>
                </table></fieldset>
                <div style='clear: both;'></div>
                

                        <input type=hidden id=methodpph value='insert'>
				<table>
				<fieldset style='float:left'>
		        <legend>".$_SESSION['lang']['list']."</legend>
		        <div id=containerpph> 
		            <script>loadDatapph(0)</script>
		        </div>
		    </fieldset>
		    </table>
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
	
        /*=====Detail Form pph End =====*/

	
$hfrm[0]="Rek Bank";
$hfrm[1]="NPWP Supplier";
//$hfrm[2]="Kelompok Supplier";
//$hfrm[2]="Timbangan Supplier";
$hfrm[2]="Alamat Supplier";
$hfrm[3]="Pajak";
drawTab('FRM2',$hfrm,$frm,170,'');
break;
case 'listupload' :

$lampiran = "";
$strlampiran =$owlPDO->query("select a.kode_jenis,a.badanusaha,a.nama_jenis,IFNULL(b.namafile,'') as namafile
						from ".$dbname.".log_5jenislampiran a
						left join ".$dbname.".log_fileupload b on b.idlampiran = a.kode_jenis and b.supplierid = '".$id_supplier."'
						");
$strlampiran->setFetchMode(PDO::FETCH_OBJ);
$jmlLam = $strlampiran->rowCount();

?>
<div id='' class='x-box-blue' style='clear: both; width:1000px;'>
	<div class='x-box-tl'>
		<div class='x-box-tr'>
			<div class='x-box-tc'></div>
		</div>
	</div>
	<div class='x-box-ml'>
		<div class='x-box-mr'>
			<div class='x-box-mc' id='contentBox' style='overflow:auto; '>
			
				<table class="sortable" border="0" cellspacing="1" cellpadding="0" style="width:100%;">
					<thead>
						<tr class="rowheader">
							<th width="1" align="center">No</th>
							<th align="left">Jenis Lampiran</th>
							<th width="200" align="center">File</th>
							<th width="1" align="center"></th>
						</tr>
					</thead>
					<tbody>
					<?php 
					if($jmlLam>0){
						$num = 1;
						$path = "fileupload/supplier/".$id_supplier;
						while($r=$strlampiran->fetch()){
							$spiter = array();
							$spiter = explode(",",$r->badanusaha);
							if(in_array($badanusaha,$spiter)){
							?>
							<tr class="rowcontent" >
								<td width="1" align="center"><?php echo $num; ?></td>
								<td><?php echo $r->nama_jenis; ?></td>
								<td align="center">
									<?php 
									if($r->namafile!=""){
										echo "<a href='".$path."/".$r->namafile."' download>".$r->namafile."</a>"; 
									}else{
										?>
										<input type="file" name="file_<? echo $r->kode_jenis; ?>" id="file_<? echo $r->kode_jenis; ?>"class="myinputtext" style="max-width: 50%;">
										<input type="button" onclick="upload_fileaftersign('file_<? echo $r->kode_jenis; ?>','<?php echo $id_supplier;?>','<?php echo $namasupplier;?>','<?php echo $badanusaha;?>','<? echo $r->kode_jenis; ?>','');" class="mybutton" value="Upload" style="max-width: 50%;">
										<?php 
									}
									?>
								</td>
								<td align="center">
									<? if($r->namafile!=""){ ?>
									<a onclick="delete_fileaftersign('<?php echo $r->kode_jenis; ?>','<?php echo $id_supplier;?>','<?php echo $namasupplier;?>','<?php echo $badanusaha;?>');"><img src="images/delete_32.png" class="resicon"></a>
									<? } ?>
								</td>
							</tr>
					<?php
							$num++;
							}
						} 
					} ?>
					</tbody>
					<?php
					$strlegitimate =$owlPDO->query("select supplierid,idlampiran,'legitimate' as badanusaha,'File After Sign' as nama_jenis,namafile
							from ".$dbname.".log_fileupload where lokasifile = 'legitimate' 
							and supplierid = '".$id_supplier."' ");
					$strlegitimate->setFetchMode(PDO::FETCH_OBJ);
					$jmlLg = $strlegitimate->rowCount();
					if($jmlLg>0){
						$path = "fileupload/supplier/".$id_supplier;
						echo "<tfoot>";
						while($r=$strlegitimate->fetch()){
							$tgl = date("YmdHis");
							?>
							<tr class="rowcontent" >
								<td width="1" align="center"><img src="images/newfile.png" class="resicon"></td>
								<td><?php echo $r->nama_jenis; ?></td>
								<td align="center">
									<?php echo "<a href='".$path."/".$r->namafile."?tgl=".$tgl."' download>".$r->namafile."</a>"; ?>
								</td>
								<td align="center">
									<a onclick="delete_fileaftersign('<?php echo $r->idlampiran; ?>','<?php echo $id_supplier;?>','<?php echo $namasupplier;?>','<?php echo $badanusaha;?>');"><img src="images/delete_32.png" class="resicon"></a>
								</td>
							</tr>
					<?php
						$num++;
						} 
					echo "</tfoot>
				</table>";
					}else{ ?>
					</table>
						<br>
						<label><img src="images/newfile.png"> Register File After Sign : </label>
						<input type="file" name="fileaftersign" id="fileaftersign" class="myinputtext">
						<input type="button" onclick="upload_fileaftersign('fileaftersign','<?php echo $id_supplier;?>','<?php echo $namasupplier;?>','<?php echo $badanusaha;?>','0','legitimate');" class="mybutton" value="Upload">
				<?php 					
					}
					?>
			</div>
		</div>
	</div>
	<div class="x-box-bl">
		<div class="x-box-br">
			<div class="x-box-bc"></div>
		</div>
	</div>
</div>
	<?php 
break;
case 'deletefile':
	$path = "fileupload/supplier/".$id_supplier."/";
	$select = "select namafile from ".$dbname.".log_fileupload where idlampiran = '".$idlampiran."' and supplierid = '".$id_supplier."'";
	$strlampiran = $owlPDO->query($select);
	$strlampiran->setFetchMode(PDO::FETCH_OBJ);
	while($r=$strlampiran->fetch()){
		if($r->namafile != ""){
			unlink($path.$r->namafile);
		}
	}
	$str = "delete FROM ".$dbname.".log_fileupload where idlampiran = '".$idlampiran."' and supplierid = '".$id_supplier."'";
	try{
		$owlPDO->exec($str);
	}catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
break;
case 'uploadfile':
	$tgl = date("YmdHis");
	
    $data = $_POST;
    
    if($data['fileupload']!='')
    {
      if($_FILES['file']['error']==0)
      {
        $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
        //$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
		if($lokasifile == "legitimate"){
			$filename = "after_sign_".$id_supplier.$filetype;
		}else{
			$filename = basename(str_less($_FILES['file']['name']));
        }
        $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
        if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')){
          if($_FILES['file']['size'] <= 2500000){
			  
			  $path = "fileupload/supplier/".$id_supplier."/";
			  if (!file_exists($path)) {
					mkdir($path,0777,true);
				}
			//$f = finfo_open();
			//$mime_type = finfo_buffer($f, $file_tmpname, FILEINFO_MIME_TYPE);
			if(file_put_contents($path.$filename, $file_tmpname)){
				$str = "insert into ".$dbname.".log_fileupload (supplierid,idlampiran,namafile,tipefile,size,lokasifile,createdtime) values ('".$id_supplier."','".$idlampiran."','".$filename."','".$_FILES['file']['type']."','".$_FILES['file']['size']."','".$lokasifile."','".date('Y-m-d H:i')."')";
				try{
					$owlPDO->exec($str);
				}catch(PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
			//exit("TEST by Admin: Warning - ".$mime_type);
			  
			/*  
			if($file_tmpname){
				$path = "fileupload/supplier/".$id_supplier."/";
				if (!file_exists($path)) {
					mkdir($path,0777,true) || chmod($path, 0777);
				}
				if(move_uploaded_file($file_tmpname,$path.$filename)){
					 $str = "insert into ".$dbname.".log_fileupload (supplierid,idlampiran,namafile,tipefile,size,lokasifile,createdtime) values ('".$id_supplier."','".$idlampiran."','".$filename."','".$_FILES['file']['type']."','".$_FILES['file']['size']."','".$lokasifile."','".date('Y-m-d H:i')."')";
					try{
						$owlPDO->exec($str);
					}catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}
			}*/
          }else{
            exit("warning : Ukuran file upload maksimal 2,5 Mb");
          }
        }else{
          exit("Warning : Format file upload harus jpg,png,pdf");
        }
      }
    }
break;
}
?>
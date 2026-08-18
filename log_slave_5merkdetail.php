<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$user_entry=$_SESSION['standard']['userid'];
$idmerk = false;
$idmerk = checkPostGet('idmerk','');
$merk = checkPostGet('merk','');
$prosses = '';
$prosses = checkPostGet('prosses','');
if($idmerk == false){
	exit();
}

$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodebarang,namabarang FROM " . $dbname . ".log_5masterbarang where inactive=1 order by kodebarang asc";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value=" . $bar['kodebarang'] . ">" . $bar['kodebarang'] . " - " . $bar['namabarang'] . "</option>";
}
		echo "<div id='' class='x-box-blue' style='clear: both;'>
				<div class='x-box-tl'>
					<div class='x-box-tr'>
						<div class='x-box-tc'></div>
					</div>
				</div>
			<div class='x-box-ml'>
				<div class='x-box-mr'>
					<div class='x-box-mc' id='contentBox' style='overflow:auto;'>
					<span class='judul'>".strtoupper($_SESSION['lang']['detail']." ".$_SESSION['lang']['namabarang'])."</span><br>
					
				<fieldset>
					<legend>Form</legend>
					<input id='methoddetail' type='hidden' class='myinputtext' name='prosses'  value='insert'>
					<input id='detailmerk' type='hidden' class='myinputtext' name='detailmerk'  value='<?php echo $idmerk; ?>'>
						<table>

							<tr>
								<td>ID ".$_SESSION['lang']['merk']."</td>
								<td>:</td>
								<td><input type=text id=idmerk_det nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" value='".$idmerk."' disabled>
							
								<input type=text id=merk_det nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:225px;\" value='".$merk."' disabled></td>
								
							</tr>
							
							<tr>
								<td>".$_SESSION['lang']['namabarang']."</td> 
								<td>:</td>
								<td><select type=text  id=kodebarang_det  style=\"width:313px;\">".$optorg."</select>
								<img id='kodebarang_det' onclick=z.elSearch('kodebarang_det',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>

							</tr>
							<tr><td colspan=2></td>
								<td colspan=3>
									   <button class=mybutton onclick=saveBarang()>Simpan</button>
									<button class=mybutton onclick=cancelBarang()>Reset</button>
								</td>
						</tr>
						</table>
					</form>	
				</fieldset>
					<div style='clear: both;'></div>
					
					<div style='width=100%; max-height:300px;overflow:auto; max-width:850px;'>	
					<table>
					<fieldset style='float:left'>
			        <legend>".$_SESSION['lang']['list']."</legend>
			        <div id=containerbarang> 
			            <script>loadDataBarang(0)</script>
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
?>
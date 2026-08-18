<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_5batchbibit.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');


// $optKode='';
// $sReg="select kode,nama from ".$dbname.".setup_kelaslahan where aktif='1' order by kode asc";
// $rReg=$owlPDO->query($sReg) or die(print " Gagal: ".PDOException::getMessage());
// $rReg->setFetchMode(PDO::FETCH_OBJ);
// while($bReg=$rReg->fetch()){
// 	$optKode.="<option value='".$bReg->kode."'>".$bReg->kode." - ".$bReg->nama."</option>";	
// }

// $optthntnm = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $sThn="SELECT distinct tahuntanam FROM $dbname.`setup_blok` ORDER BY tahuntanam asc";
// $rThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
// $rThn->setFetchMode(PDO::FETCH_OBJ);
// while($bThn=$rThn->fetch()){
// 	$optthntnm.="<option value='".$bThn->tahuntanam."'>".$bThn->tahuntanam."</option>";	
// }

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5batchbibit').'</span>');
echo"<div>
        <fieldset style=width:350px;float:left;>
            <legend>".$_SESSION['lang']['form']."</legend>
            <table>
                <tr>
                    <td>".$_SESSION['lang']['kode']."</td><td>:</td>
                    <td>
                        <input type='text' id='kode' class='myinputtext' onkeypress=\"return tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:200px' maxlength='9' value='' />
                    </td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['nama']."</td><td>:</td>
                    <td>
                        <input type='text' id='nama' class='myinputtext' onkeypress=\"return tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:200px' value='' />
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <input type=hidden value=insert id=proses>
                        <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>
            </table>
        </fieldset>
     </div>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=width:350px;float:left;><legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>
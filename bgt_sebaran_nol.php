<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language="javascript">
    function proseskang()
{
	tahunbudget=document.getElementById('thnbudget');
        tahunbudget=tahunbudget.options[tahunbudget.selectedIndex].value;
        
	kodebudget=document.getElementById('kodebudget');
        kodebudget=kodebudget.options[kodebudget.selectedIndex].value;
        
	kodeorg=document.getElementById('kodeorg');
        kodeorg=kodeorg.options[kodeorg.selectedIndex].value;
        
	param='kodeorg='+kodeorg+'&kodebudget='+kodebudget+'&tahunbudget='+tahunbudget;
	//alert(param);
	tujuan='bgt_slave_save_budget_nol.php';
        if(confirm('Are you sure..?')){
            post_response_text(tujuan, param, respog);
        }
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                        }
                        else {
                          alert('Done');	
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
                }
            }	

}
</script>
<?php

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4  order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $opt_unit.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
$str="select tahunbudget from ".$dbname.".bgt_hk order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $opt_tahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}
$str="select kodebudget,nama from ".$dbname.".bgt_kode order by nama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opt_kode="<option value=''>".$_SESSION['lang']['all']."</option>";
$opt_kode.="<option value='KAPITAL'>[KAPITAL]-KAPITAL</option>";
while($bar=$res->fetch())
{
    $opt_kode.="<option value='".$bar->kodebudget."'>[".$bar->kodebudget."]-".$bar->nama."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper('BUDGET DISTRIBUTION:').'</span>');
echo "<fieldset style='width:500px;'><legend>".$_SESSION['lang']['form']."</legend>
      <table>
        <tr>
          <td>".$_SESSION['lang']['unit']."</td><td><select id=kodeorg>".$opt_unit."</select></td>
        </tr>
        <tr>
          <td>".$_SESSION['lang']['tahunanggaran']."</td><td><select id=thnbudget>".$opt_tahun."</select></td>
        </tr>
        <tr>
          <td>".$_SESSION['lang']['kodebudget']."</td><td><select id=kodebudget>".$opt_kode."</select></td>
        </tr>
      </table>
      <button class=mybutton onclick=proseskang()>".$_SESSION['lang']['proses']."</button>
      <hr>";
if($_SESSION['language']=='ID'){
        echo"      Note:Seluruh budget unit bersangkutan akan di sebar merata setiap bulan baik fisik maupun harga";
}else{
        echo"      Note:The entire budget units concerned will be spread evenly each month both physical and price";
}
 echo"     </fieldset>";

CLOSE_BOX();
echo close_body();
?>
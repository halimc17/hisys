<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>




<script language=javascript src=js/zTools.js></script>
<script>

function simpan()
{
    pasar=document.getElementById('pasar').value;
    komoditi=document.getElementById('komoditi').value;
    satuan=document.getElementById('satuan').value;
    sumber=document.getElementById('sumber').value;
    if(pasar=='' || komoditi==''||sumber=='')
    {
            alert('field pasar or komoditi is Empty');
            return;
    }

	param='method=insert'+'&pasar='+pasar+'&komoditi='+komoditi+'&satuan='+satuan+'&sumber='+sumber;
    tujuan='pmn_slave_5pasar.php';
    //alert(param);
    post_response_text(tujuan, param, respog);		
	
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else {
							 //document.location.reload();
                                                       	 document.getElementById('pasar').value='';
                                                       	 document.getElementById('komoditi').selectedIndex='0';
                                                       	 document.getElementById('satuan').value='';
                                                       	 document.getElementById('sumber').value='';

                                                         loadData();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}

function loadData () 
{
	param='method=loadData';
	tujuan='pmn_slave_5pasar.php';
    post_response_text(tujuan, param, respog);
	function respog()
	{
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                }
                                else {
                                   // alert(con.responseText);
                                    document.getElementById('komoditi').value='';
                                    document.getElementById('container').innerHTML=con.responseText;
									
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

   

function del(pasar)
{
	param='method=delete'+'&pasar='+pasar;
	tujuan='pmn_slave_5pasar.php';
	post_response_text(tujuan, param, respog);	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else 
					{
						loadData();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}   

function getsatuan(val)
{
	param='method=getsatuan&komoditi='+val;
	tujuan='pmn_slave_5pasar.php';
	post_response_text(tujuan,param,respog);
	function respog()
	{
		if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else 
					{
						document.getElementById('satuan').value=trim(con.responseText);
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
$arkomoditi = makeOption($dbname,"log_5masterbarang","kodebarang,namabarang","kelompokbarang='400' or left(kodebarang,5)='35101' ","",true);
foreach ($arkomoditi as $key => $val) {
	$optkomoditi.="<option value=".$key.">".$val."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('pmn_5pasar').'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<br><fieldset style='float:left;'>";
    echo"<legend>".$_SESSION['lang']['entryForm']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                 
                <tr>
                    <td>".$_SESSION['lang']['pasar']."</td> 
                    <td>:</td>
                    <td><input type=text  id=pasar onkeypress=\"return tanpa_kutip(event);\"   class=myinputtext style=\"width:100px;\"></td>
                </tr>
                <tr>
                	<td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['komoditi']."</td> 
                    <td>:</td>
                    <td><select  id=komoditi style=\"width:200px;\" onchange=\"getsatuan(this.value);\">".$optkomoditi."</select></td>
                </tr>
                <tr>
                	<td>Sumber".$_SESSION['lang']['']."</td> 
                    <td>:</td>
                    <td><input type=text  id=sumber style=\"width:200px;\" onkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:50px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['satuan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=satuan onkeypress=\"return tanpa_kutip(event);\"   class=myinputtext style=\"width:50px;\" disabled></td>
                </tr>
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                              
                        </td>
                </tr>

        </table></fieldset>";
       

CLOSE_BOX();//                        <input type=hidden id=method value='insert'>
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>
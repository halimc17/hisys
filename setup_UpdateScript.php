<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script type="text/javascript">
function updateSNow()
{
    param='method=update';
    
    tujuan='setup_slave_update_script.php';
    post_response_text(tujuan, param, respog);    
    function respog()
    {
      if(con.readyState==4)
      {
        if (con.status == 200) {
        	document.getElementById('remat').innerHTML='';
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                            
                    }
                    else {
                        alert(con.responseText);
                        document.getElementById('remat').innerHTML=con.responseText;
                        alert('Script updated');
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
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>Update Script from SVN</span>');
echo "<fieldset><legend>Update Script</legend>
		<button onclick='updateSNow();'>Update Now</button> 
		<div id=remat></div>
	 </fieldset>";
CLOSE_BOX();
echo close_body();
?>
/**
 * @author repindra.ginting
 */
function simpanInterval()
{
	interval=document.getElementById('interval').value;
	alo=document.getElementById('alo');
	alo=alo.options[alo.selectedIndex].value;
	interval=interval==''?0:interval;

	param='interval='+interval+'&alo='+alo;
	tujuan='slave_gpsInterval.php';
	if(interval>'10000'){
    	post_response_text(tujuan, param, respog);		
	}else{
		alert('Minimun 10,000');
	}
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							//alert(con.responseText);
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

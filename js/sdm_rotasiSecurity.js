/**
 * @author {nangkoel@gmail.com}
 * jakarta indonesia
 */

function cariNama()
{
	txtnama=document.getElementById('txtnama').value;
	if(txtnama=='' || txtnama.length<3)
	{
		 alert('Min. 3 char');
	}
	else
	{
		param='txtnama='+txtnama;
		tujuan='sdm_slave_cariSecurity.php';
	    post_response_text(tujuan, param, respog);		
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

function setKarTo(karid)
{
	pt=document.getElementById('tujuan'+karid).options[document.getElementById('tujuan'+karid).selectedIndex].value;
	lokasitugas=document.getElementById('tujuan'+karid).options[document.getElementById('tujuan'+karid).selectedIndex].text;
   if(confirm('Are you sure,?') && pt!='*')
   {
		param='karyawanid='+karid+'&pt='+pt+'&lokasitugas='+lokasitugas;
		tujuan='sdm_slave_save_rotasiSecurity.php';
	    post_response_text(tujuan, param, respog);		
	}
	
	function respog()
	{   
		      if(con.readyState==4)
		      {		       
				   if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
							document.getElementById('tujuan'+karid).options[0].selected=true;
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

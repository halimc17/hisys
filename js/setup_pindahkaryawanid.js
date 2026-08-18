/**
 * @author ThIS
 */
function gantiJabatan()
{

	   //tjbaru=document.getElementById('tjbaru').options[document.getElementById('tjbaru').selectedIndex].value;
	   jabatanbaru=document.getElementById('jabatanbaru').options[document.getElementById('jabatanbaru').selectedIndex].value;
	   jabatanbaru = jabatanbaru.split("##");

//	  alert(jabatanbaru[1]);
		param='karyawanid='+jabatanbaru[0]+'&kodeorg='+jabatanbaru[1];
		tujuan='setup_slave_save_pindahkaryawanid.php';
        alert(param);
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
							alert(con.responseText);
							parent.window.location='logout.php';
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
		
}
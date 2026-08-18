function changediv()
{
	kdorg=document.getElementById('kdorg').options[document.getElementById('kdorg').selectedIndex].value;
	
	
	param='proses=getdiv&kdorg='+kdorg;
    tujuan='kebun_slave_2rkhvsbkm.php';
    post_response_text(tujuan, param, respog);		
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('kddiv').innerHTML=con.responseText;
				}
			}
			else 
			{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


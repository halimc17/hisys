function preview1(type)
{
	kdpabrik    =document.getElementById('kdpabrik1').value;
	kdbrg       =document.getElementById('kdbrg1').value;
	cust        =document.getElementById('cust1').value;
	tgltrans    =document.getElementById('tgltrans1').value;
	tgltrans2   =document.getElementById('tgltrans2').value;
	param       ='kdpabrik='+kdpabrik+'&kdbrg='+kdbrg+'&tgltrans='+tgltrans+'&cust='+cust+'&proses=preview1&type='+type+'&tgltrans2='+tgltrans2;
	tujuan      ='pabrik_slave_2timbanganeks.php';
    
    if(type == 'pdf'){
	    alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+'?'+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
    }else{
        printnopopup(tujuan+'?'+param)
    }
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
					document.getElementById('contain').innerHTML=con.responseText;
					leftFixedTable();
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
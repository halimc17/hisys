// JavaScript Document

function getafdeling()
{
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	
	param='unit='+unit+'&proses=getafdeling';
	tujuan='sdm_slave_2bkmglobal.php';
	post_response_text(tujuan, param, respon);
	
	function respon() 
	{
		if (con.readyState == 4) 
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
					document.getElementById('afdeling').innerHTML = con.responseText;
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

function preview()
{
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	afdeling=document.getElementById('afdeling').options[document.getElementById('afdeling').selectedIndex].value;
	tglawal=document.getElementById('tglawal').value;
	tglakhir=document.getElementById('tglakhir').value;
	nobkm=document.getElementById('nobkm').value;
	
	param='unit='+unit+'&afdeling='+afdeling+'&tglawal='+tglawal+'&tglakhir='+tglakhir+'&nobkm='+nobkm+'&proses=preview&type=html';
	tujuan='sdm_slave_2bkmglobal.php';
	post_response_text(tujuan, param, respon);
	
	function respon() 
	{
		if (con.readyState == 4) 
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
					document.getElementById('container').innerHTML = con.responseText;
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

function excel(ev)
{
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	afdeling=document.getElementById('afdeling').options[document.getElementById('afdeling').selectedIndex].value;
	tglawal=document.getElementById('tglawal').value;
	tglakhir=document.getElementById('tglakhir').value;
	nobkm=document.getElementById('nobkm').value;
	
	param='unit='+unit+'&afdeling='+afdeling+'&tglawal='+tglawal+'&tglakhir='+tglakhir+'&nobkm='+nobkm+'&proses=preview&type=excel';
	tujuan='sdm_slave_2bkmglobal.php';
	
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev)	
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
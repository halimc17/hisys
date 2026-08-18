// JavaScript Document
function getpic(pic)
{
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	departemen = document.getElementById('departemen').options[document.getElementById('departemen').selectedIndex].value;
	
	param='method=getpic&pt='+pt+'&departemen='+departemen+'&pic='+pic;
	tujuan='sdm_slave_5reminder.php';
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
					document.getElementById('pic').innerHTML=con.responseText;
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

function getemail()
{
	pic = document.getElementById('pic').options[document.getElementById('pic').selectedIndex].value;
	
	param='method=getemail&pic='+pic;
	tujuan='sdm_slave_5reminder.php';
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
					document.getElementById('email').value=con.responseText;
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

function batal()
{
	document.getElementById('method').value='insert';
	document.getElementById("pt").selectedIndex = "0";
	document.getElementById("departemen").selectedIndex = "0";
	document.getElementById('email').value='';
	document.getElementById('pt').disabled = false;
	document.getElementById('departemen').disabled = false;
	document.getElementById('pic').disabled = false;
	
	getpic();
}

function loaddata(){
	param='method=loaddata';
	tujuan='sdm_slave_5reminder.php';
	post_response_text(tujuan, param, respog);
	
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
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function edit(pt,departemen,pic,email)
{
	xpt = document.getElementById('pt');
    for(x=0;x<xpt.length;x++)
    {
        if(xpt.options[x].value==pt)
		{
			xpt.options[x].selected=true;
		}
    }
	
	xdepartemen = document.getElementById('departemen');
    for(x=0;x<xdepartemen.length;x++)
    {
        if(xdepartemen.options[x].value==departemen)
		{
			xdepartemen.options[x].selected=true;
		}
    }
	getpic(pic);
	document.getElementById('email').value = email;
	document.getElementById('method').value='edit';
	
	document.getElementById('pt').disabled = true;
	document.getElementById('departemen').disabled = true;
	document.getElementById('pic').disabled = true;
}

function simpan()
{
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	departemen=document.getElementById('departemen').options[document.getElementById('departemen').selectedIndex].value;
	pic=document.getElementById('pic').options[document.getElementById('pic').selectedIndex].value;
	email=trim(document.getElementById('email').value);
	method=trim(document.getElementById('method').value);
	
	param='pt='+pt+'&departemen='+departemen+'&pic='+pic+'&email='+email+'&method='+method;
	tujuan='sdm_slave_5reminder.php';
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
					document.getElementById('container').innerHTML=con.responseText;
					loaddata();
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

function hapus(pic){
	param='pic='+pic+'&method=hapus';
	tujuan='sdm_slave_5reminder.php';
	if(confirm('Anda yakin hapus item ini?'))
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
				}else{
					loaddata();
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
// JavaScript Document
function batal(){
	document.getElementById('method').value='insert';
	document.getElementById("kd_org").value = "";
	document.getElementById('kdorg').disabled=false;
	document.getElementById('ip').value='';
	document.getElementById('username').value='';
	document.getElementById('password').value='';
	document.getElementById('dbnm').value='';
	document.getElementById('tblnm').value='';
	document.getElementById('port').value='';
	document.getElementById('id').value='';
}

function simpan(){
	kdorg=document.getElementById('kd_org').value;
	ip=trim(document.getElementById('ip').value);
	username=trim(document.getElementById('username').value);
	password=trim(document.getElementById('password').value);
	dbnm=trim(document.getElementById('dbnm').value);
	tblnm=trim(document.getElementById('tblnm').value);
	port=trim(document.getElementById('port').value);
	method=trim(document.getElementById('method').value);
	id=trim(document.getElementById('id').value);
	
	param='kdorg='+kdorg+'&ip='+ip+'&username='+username+'&password='+password+'&dbnm='+dbnm+'&tblnm='+tblnm+'&port='+port+'&method='+method+'&id='+id;
	tujuan='sdm_slave_5ipfinger.php';
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
					batal();
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

function loaddata(){
	param='method=loaddata';
	tujuan='sdm_slave_5ipfinger.php';
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
					batal();
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

function fillfield(id,kdorg,ip,username,password,dbnm,tblnm,port){
	// Lkdorg=document.getElementById('kdorg');
    // for(ard=0;ard<Lkdorg.length;ard++)
    // {
        // if(Lkdorg.options[ard].value==kdorg)
		// {
			// Lkdorg.options[ard].selected=true;
		// }
    // }	
	// document.getElementById('kdorg').disabled=true;
	
	document.getElementById('id').value=id;
	document.getElementById('kd_org').value=kdorg;
	document.getElementById('ip').value=ip;
	document.getElementById('username').value=username;
	document.getElementById('password').value=password;
	document.getElementById('dbnm').value=dbnm;
	document.getElementById('tblnm').value=tblnm;
	document.getElementById('port').value=port;
	document.getElementById('method').value='edit';
}

function deletefield(kdorg){
	param='id='+kdorg+'&method=delete';
	tujuan='sdm_slave_5ipfinger.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
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
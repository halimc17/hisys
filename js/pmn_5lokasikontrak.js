function batal()
{
	document.getElementById('lokasi').value = '';
	document.getElementById('lokasi').disabled = false;
    document.getElementById('inisial').value = '';
	document.getElementById('inisial').disabled = false;
	document.getElementById('status').checked = true;
    document.getElementById('method').value='insert';
}

function loaddata(num) 
{
	param='method=loaddata';
    param+='&page='+num;
    tujuan='pmn_slave_5lokasikontrak.php';
	
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

function simpan()
{
    lokasi = document.getElementById('lokasi').value;
    inisial = document.getElementById('inisial').value;
    aktif = document.getElementById('status');
    
	if(aktif.checked==true)
	{
		aktif=1;
	}
    else
	{
		aktif=0;
	}
    method=document.getElementById('method').value;

    if(lokasi=='' || inisial=='')
    {
		alert('Field Was Empty');
        return false;
    }
	
	param='lokasi='+lokasi+'&inisial='+inisial+'&status='+aktif+'&method='+method;
    tujuan='pmn_slave_5lokasikontrak.php';
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
					alert("Success");
					batal();
                    loaddata(0);
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

function edit(id,lokasi,inisial,aktif)
{
	document.getElementById('lokasi').value=lokasi;
	document.getElementById('lokasi').disabled=true;
    document.getElementById('inisial').value=inisial;
	document.getElementById('inisial').disabled=true;
    
	if(aktif=='1')
	{
		document.getElementById('status').checked=true;
	}
    else
	{
		document.getElementById('status').checked=false;
	}
	document.getElementById('method').value='update';
}
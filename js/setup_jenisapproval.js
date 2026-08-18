function batal()
{
	document.getElementById('jenis').value = '';
	document.getElementById('nama').value = '';
	document.getElementById('jenis').disabled = false;
	document.getElementById('nama').disabled = false;
	document.getElementById('status').checked = true;
    document.getElementById('method').value='insert';
}

function loaddata(num) 
{
	param='method=loaddata';
    param+='&page='+num;
    tujuan='setup_salve_jenisapproval.php';
	
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
    jenis = document.getElementById('jenis').value;
    nama = document.getElementById('nama').value;
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

    if(jenis=='')
    {
		alert('Field Was Empty');
        return false;
    }
	
	param='nama='+nama+'&jenis='+jenis+'&status='+aktif+'&method='+method;
    tujuan='setup_salve_jenisapproval.php';
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

function edit(jenis,nama,aktif)
{
	document.getElementById('jenis').value=jenis;
	document.getElementById('nama').value=nama;
	document.getElementById('jenis').disabled=true;
    
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
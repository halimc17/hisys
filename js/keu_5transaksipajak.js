function simpan()
{
    id=document.getElementById('id').value;
    jenis=document.getElementById('jenis').value;
    method=document.getElementById('method').value;
    aktif = document.getElementById('status');

    if(aktif.checked==true){
		aktif=1;
	}else{
		aktif=0;
	}

    if(jenis=='')
    {
        alert('Field Was Empty');
        return;
    }

    param='id='+id+'&jenis='+jenis+'&status='+aktif+'&method='+method;
    tujuan='keu_slave_5transaksipajak.php';
    post_response_text(tujuan, param, respog);		
	
	function respog()
	{
	    if(con.readyState==4)
	    {
	        if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancel();
                    loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
	    }	
	}
}
					
function cancel()
{
    document.getElementById('id').value='';
    document.getElementById('jenis').value='';
    document.getElementById('method').value='insert';
	document.getElementById('status').checked=true;
}

function loadData () 
{
	param='method=loadData';
	tujuan='keu_slave_5transaksipajak.php';
    post_response_text(tujuan, param, respog);
	function respog()
	{
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
	}  
}

function edit(id,jenis,aktif)
{
    document.getElementById('id').value=id;
    document.getElementById('id').disabled=true;
    document.getElementById('jenis').value=jenis;
    document.getElementById('method').value='update';

    if(aktif=='1')
	{
		document.getElementById('status').checked=true;
	}
    else
	{
		document.getElementById('status').checked=false;
	}
}

function del(id)
{
	param='method=delete'+'&id='+id;
	tujuan='keu_slave_5transaksipajak.php';
	post_response_text(tujuan, param, respog);	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}





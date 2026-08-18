function simpan()
{
    id=document.getElementById('id').value;
    jenis=document.getElementById('jenis').value;
    ket=document.getElementById('ket').value;
    method=document.getElementById('method').value;

    if(jenis=='' || ket=='')
    {
        alert('Field Was Empty');
        return;
    }

    param='id='+id+'&jenis='+jenis+'&ket='+ket+'&method='+method;
    tujuan='keu_slave_5jenisdisposalasset.php';
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
					cancel();
		            loadData();
				}
			}
			else {
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
    document.getElementById('ket').value='';
    document.getElementById('method').value='insert';
    document.getElementById('jenis').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='keu_slave_5jenisdisposalasset.php';
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
                                   // alert(con.responseText);
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

function edit(id,jenis,ket)
{
    document.getElementById('id').value=id;
    document.getElementById('id').disabled=true;
    document.getElementById('jenis').value=jenis;
    document.getElementById('jenis').disabled=true;
     document.getElementById('ket').value=ket;
    document.getElementById('method').value='update';
}



function del(id)
{
	param='method=delete'+'&id='+id;
	tujuan='keu_slave_5jenisdisposalasset.php';
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
				else 
				{
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}

}





//JS 


function simpan(){
    parameter=document.getElementById('parameter').value;
    ket=document.getElementById('ket').value;
   
    method=document.getElementById('method').value;

    if(parameter=='' || ket=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='parameter='+parameter+'&ket='+ket+'&method='+method;
    tujuan='pabrik_slave_5mr_roa_parameter.php';
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
    document.getElementById('parameter').value='';
    document.getElementById('ket').value='';
    document.getElementById('method').value='insert';
    document.getElementById('parameter').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_5mr_roa_parameter.php';
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

function edit(parameter,ket)
{
    document.getElementById('parameter').value=parameter;
    document.getElementById('parameter').disabled=true;
     document.getElementById('ket').value=ket;
    document.getElementById('method').value='update';
}



function del(parameter)
{
	param='method=delete'+'&parameter='+parameter;
	tujuan='pabrik_slave_5mr_roa_parameter.php';
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





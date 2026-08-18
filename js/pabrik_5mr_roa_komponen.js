//JS 


function simpan(){
    komponen=document.getElementById('komponen').value;
    ket=document.getElementById('ket').value;
   
    method=document.getElementById('method').value;

    if(komponen=='' || ket=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='komponen='+komponen+'&ket='+ket+'&method='+method;
    tujuan='pabrik_slave_5mr_roa_komponen.php';
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
    document.getElementById('komponen').value='';
    document.getElementById('ket').value='';
    document.getElementById('method').value='insert';
    document.getElementById('komponen').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_5mr_roa_komponen.php';
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

function edit(komponen,ket)
{
    document.getElementById('komponen').value=komponen;
    document.getElementById('komponen').disabled=true;
     document.getElementById('ket').value=ket;
    document.getElementById('method').value='update';
}



function del(komponen)
{
	param='method=delete'+'&komponen='+komponen;
	tujuan='pabrik_slave_5mr_roa_komponen.php';
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





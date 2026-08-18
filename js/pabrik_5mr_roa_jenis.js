//JS 


function simpan(){
    jenis=document.getElementById('jenis').value;
    ket=document.getElementById('ket').value;
   
    method=document.getElementById('method').value;

    if(jenis=='' || ket=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='jenis='+jenis+'&ket='+ket+'&method='+method;
    tujuan='pabrik_slave_5mr_roa_jenis.php';
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
    document.getElementById('jenis').value='';
    document.getElementById('ket').value='';
    document.getElementById('method').value='insert';
    document.getElementById('jenis').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_5mr_roa_jenis.php';
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

function edit(jenis,ket)
{
    document.getElementById('jenis').value=jenis;
    document.getElementById('jenis').disabled=true;
     document.getElementById('ket').value=ket;
    document.getElementById('method').value='update';
}



function del(jenis)
{
	param='method=delete'+'&jenis='+jenis;
	tujuan='pabrik_slave_5mr_roa_jenis.php';
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





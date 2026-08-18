//JS 


function simpan()
{
    kode=document.getElementById('kode').value;
    ket=document.getElementById('ket').value;
	jumlahhari=document.getElementById('jumlahhari').value
    method=document.getElementById('method').value;

    if(kode=='' || ket=='' || jumlahhari <= 0)
    {
            alert('Field Was Empty');
            return;
    }

    param='kode='+kode+'&ket='+ket+'&jumlahhari='+jumlahhari+'&method='+method;
    tujuan='log_slave_5delivtime.php';
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
   
    document.getElementById('kode').value='';
    document.getElementById('ket').value='';
    document.getElementById('jumlahhari').value='';
    document.getElementById('method').value='insert';
    document.getElementById('kode').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='log_slave_5delivtime.php';
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

function edit(kode,ket)
{
    document.getElementById('kode').value=kode;
    document.getElementById('kode').disabled=true;
     document.getElementById('ket').value=ket;
    document.getElementById('method').value='update';
}



function del(kode)
{
	param='method=delete'+'&kode='+kode;
	tujuan='log_slave_5delivtime.php';
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





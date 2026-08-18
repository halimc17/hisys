//JS 

function simpan()
{
    regional=document.getElementById('regional').value;
    rupiah=document.getElementById('rupiah').value;
    method=document.getElementById('method').value;

    if(regional=='' || rupiah=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='regional='+regional+'&rupiah='+rupiah+'&method='+method;
    tujuan='sdm_slave_5uangmakan.php';
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
    document.getElementById('regional').value='';
    document.getElementById('rupiah').value='';
    document.getElementById('method').value='insert';
    document.getElementById('regional').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='sdm_slave_5uangmakan.php';
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

function edit(regional,rupiah)
{
    document.getElementById('regional').value=regional;
    document.getElementById('regional').disabled=true;
    document.getElementById('rupiah').value=rupiah;
    document.getElementById('method').value='update';
}



function del(regional)
{
	param='method=delete'+'&regional='+regional;
	tujuan='sdm_slave_5uangmakan.php';
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





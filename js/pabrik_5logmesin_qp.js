//JS 


function simpan()
{
    unit=document.getElementById('unit').value;
    st=document.getElementById('st').value;
    ht=document.getElementById('ht').value;
	dt=document.getElementById('dt').value;
	nil=document.getElementById('nil').value;
    method=document.getElementById('method').value;

    if(st=='' || ht=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='st='+st+'&ht='+ht+'&method='+method+'&dt='+dt+'&nil='+nil+'&unit='+unit;
    tujuan='pabrik_slave_5logmesin_qp.php';
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
					


function cancel(){   
    document.getElementById('unit').value='';
    document.getElementById('st').value='';
    document.getElementById('dt').value='0';
	document.getElementById('nil').value='';
	document.getElementById('ht').value='';
    document.getElementById('method').value='insert';
    document.getElementById('st').disabled=false; 
	document.getElementById('unit').disabled=false;
}




function loadData () {
	param='method=loadData';
	tujuan='pabrik_slave_5logmesin_qp.php';
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

function edit(unit,st,ht,dt,nil)
{
    document.getElementById('st').value=st;
    document.getElementById('st').disabled=true;
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
     document.getElementById('ht').value=ht;
	 
    document.getElementById('dt').disabled=true;
	 document.getElementById('dt').value=dt;
	 
    document.getElementById('ht').disabled=true;
	 document.getElementById('nil').value=nil;
    document.getElementById('method').value='update';
}



function del(unit,st)
{
	param='method=delete'+'&unit='+unit+'&st='+st;
	tujuan='pabrik_slave_5logmesin_qp.php';
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





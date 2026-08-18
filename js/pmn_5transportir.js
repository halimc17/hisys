//JS 

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'pmn_slave_5transportir.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                    //displayList();

                                    document.getElementById('container').innerHTML=con.responseText;
                                    //loadData();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }	
}

function simpan()
{
    tran=document.getElementById('tran').value;
    nopol=document.getElementById('nopol').value;
    driv=document.getElementById('driv').value;
    method=document.getElementById('method').value;

    if(tran=='' || nopol=='' || driv=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='tran='+tran+'&nopol='+nopol+'&driv='+driv+'&method='+method;
    tujuan='pmn_slave_5transportir.php';
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
    document.getElementById('tran').value='';
    document.getElementById('driv').value='';
    document.getElementById('nopol').value='';
    document.getElementById('method').value='insert';
}




function loadData () 
{
	param='method=loadData';
	tujuan='pmn_slave_5transportir.php';
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




function del(tran,nopol)
{
	param='method=delete'+'&tran='+tran+'&nopol='+nopol;
	tujuan='pmn_slave_5transportir.php';
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





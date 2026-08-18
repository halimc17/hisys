//JS 

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'log_slave_5rekbank.php';
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
    sup=document.getElementById('sup').value;
    bank=document.getElementById('bank').value;
    rek=document.getElementById('rek').value;
	an=document.getElementById('an').value;
    method=document.getElementById('method').value;

    if(sup=='' || bank=='' || rek=='' || an=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='sup='+sup+'&bank='+bank+'&rek='+rek+'&an='+an+'&method='+method;
    tujuan='log_slave_5rekbank.php';
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
    document.getElementById('an').value='';
    document.getElementById('bank').value='';
	document.getElementById('sup').value='';
	document.getElementById('rek').value='';
    document.getElementById('method').value='insert';
    document.getElementById('sup').disabled=false;
	document.getElementById('bank').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='log_slave_5rekbank.php';
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

function edit(sup,bank,rek,an){
    document.getElementById('sup').value=sup;
    document.getElementById('sup').disabled=true;
	document.getElementById('bank').value=bank;
    document.getElementById('bank').disabled=true;
    document.getElementById('rek').value=rek;
    document.getElementById('an').value=an;
    document.getElementById('method').value='update';
}



function del(sup,bank){
	param='method=delete'+'&sup='+sup+'&bank='+bank;
	tujuan='log_slave_5rekbank.php';
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





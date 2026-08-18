//JS 

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'kebun_slave_penjualanTBS.php';
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
    unit=document.getElementById('unit').value;
    sup=document.getElementById('sup').value;
    per=document.getElementById('per').value;
    kg=document.getElementById('kg').value;
    method=document.getElementById('method').value;

    if(unit=='' || sup=='' || per=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='unit='+unit+'&sup='+sup+'&per='+per+'&kg='+kg+'&method='+method;
    tujuan='kebun_slave_penjualanTBS.php';
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
    document.getElementById('unit').value='';
    document.getElementById('sup').value='';
    document.getElementById('per').value='';
    document.getElementById('kg').value='0';
    document.getElementById('method').value='insert';
    document.getElementById('unit').disabled=false;
    document.getElementById('sup').disabled=false;
    document.getElementById('per').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='kebun_slave_penjualanTBS.php';
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

function edit(unit,sup,per,kg)
{
    document.getElementById('unit').value=unit;
    document.getElementById('unit').disabled=true;
    document.getElementById('per').value=per;
    document.getElementById('per').disabled=true;
    document.getElementById('sup').value=sup;
    document.getElementById('sup').disabled=true;
    document.getElementById('kg').value=kg;
    document.getElementById('method').value='update';
}



function del(unit,sup,per)
{
	param='method=delete'+'&unit='+unit+'&per='+per+'&sup='+sup;
	tujuan='kebun_slave_penjualanTBS.php';
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





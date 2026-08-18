//JS 

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'log_slave_5syaratbayar.php';
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
    kode=document.getElementById('kode').value;
    nama=document.getElementById('nama').value;
    method=document.getElementById('method').value;

    if(kode=='' || nama=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='kode='+kode+'&nama='+nama+'&method='+method;
    tujuan='log_slave_5prioritas.php';
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
    document.getElementById('nama').value='';
    document.getElementById('method').value='insert';
    
}




function loadData () 
{
	param='method=loadData';
	tujuan='log_slave_5prioritas.php';
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

function edit(kode,jenis,ket)
{
    document.getElementById('kode').value=kode;
    document.getElementById('kode').disabled=true;
    document.getElementById('nama').value=jenis;
    document.getElementById('method').value='update';
}



function del(kode)
{
	param='method=delete'+'&kode='+kode;
	tujuan='log_slave_5prioritas.php';
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





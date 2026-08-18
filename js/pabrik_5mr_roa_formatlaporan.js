//JS 

function simpan(){
    jenis=document.getElementById('jenis').value;
    parameter=document.getElementById('parameter').value;
    komponen=document.getElementById('komponen').value;
   
    method=document.getElementById('method').value;

    if(jenis=='' || parameter=='' || komponen==''){
            alert('Field Was Empty');
            return;
    }

    param='jenis='+jenis+'&parameter='+parameter+'&method='+method+'&komponen='+komponen;
    tujuan='pabrik_slave_5mr_roa_formatlaporan.php';
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
    document.getElementById('parameter').value='';
    document.getElementById('komponen').value='';
    document.getElementById('method').value='insert';
    document.getElementById('jenis').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_5mr_roa_formatlaporan.php';
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




function del(jenis,parameter,komponen)
{
	param='method=delete'+'&jenis='+jenis+'&parameter='+parameter+'&komponen='+komponen;
	tujuan='pabrik_slave_5mr_roa_formatlaporan.php';
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





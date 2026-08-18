function simpan()
{
    kepada=document.getElementById('kepada').value;
    alamat=document.getElementById('alamat').value;
    id=document.getElementById('id').value;
    method=document.getElementById('method').value;
    if(kepada=='' || alamat=='')
    {
            alert('Field Empty');
            return;
    }

	param='method='+method+'&kepada='+kepada+'&alamat='+alamat+'&id='+id;
    tujuan='pmn_slave_5kepada.php';
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
							 //document.location.reload();
                                                         document.getElementById('kepada').value='';
                                                         document.getElementById('alamat').value='';
                                                         loadData();
														 clearForm();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}

function fillfield(hKepada,hAlamat,hId){
	document.getElementById('kepada').value=hKepada;
	document.getElementById('alamat').value=hAlamat;
	document.getElementById('id').value=hId;
	document.getElementById('method').value='update';
}

function loadData () 
{
	param='method=loadData';
	tujuan='pmn_slave_5kepada.php';
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

   

function del(id)
{
	param='method=delete'+'&id='+id;
	tujuan='pmn_slave_5kepada.php';
	
	if(confirm("Anda yakin menghapus item ini ?"))
    {
        post_response_text(tujuan, param, respog);
    }
		
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

function clearForm(){
	document.getElementById('kepada').value='';
	document.getElementById('alamat').value='';
	document.getElementById('id').value='';
	document.getElementById('method').value='insert';
}
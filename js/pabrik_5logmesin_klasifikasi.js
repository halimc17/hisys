//JS 


function simpan()
{
    kode=document.getElementById('kode').value;
    ket=document.getElementById('ket').value;
	tipe=document.getElementById('tipe').value;
    method=document.getElementById('method').value;

    if(kode=='' || ket=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='kode='+kode+'&ket='+ket+'&method='+method+'&tipe='+tipe;
    tujuan='pabrik_slave_5logmesin_klasifikasi.php';
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
	document.getElementById('tipe').value='';
    document.getElementById('method').value='insert';
    document.getElementById('kode').disabled=false;
}




function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_5logmesin_klasifikasi.php';
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

function edit(kode,ket,tipe)
{
    document.getElementById('kode').value=kode;
    document.getElementById('kode').disabled=true;
     document.getElementById('ket').value=ket;
	 document.getElementById('tipe').value=tipe;
    document.getElementById('method').value='update';
}



function del(kode)
{
	param='method=delete'+'&kode='+kode;
	tujuan='pabrik_slave_5logmesin_klasifikasi.php';
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





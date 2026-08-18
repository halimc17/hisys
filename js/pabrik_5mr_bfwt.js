function simpan(){
    kode=document.getElementById('kode').value;
    tipewt=document.getElementById('tipewt').value;
    nama=document.getElementById('nama').value;
   
    method=document.getElementById('method').value;

    if(tipewt=='' || nama=='')
    {
            alert('Field Was Empty');
            return;
    }
    param='tipewt='+tipewt+'&nama='+nama+'&kode='+kode+'&method='+method;
    tujuan='pabrik_slave_5mr_bfwt.php';
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
    document.getElementById('tipewt').selectedIndex='0';
    document.getElementById('nama').value='';
    document.getElementById('method').value='insert';
    document.getElementById('tipewt').disabled=false;
}

function loadData () 
{
	param='method=loadData';
	tujuan='pabrik_slave_5mr_bfwt.php';
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

function edit(kode,tipewt,nama)
{
    document.getElementById('kode').value=kode;
    document.getElementById('tipewt').value=tipewt;
    document.getElementById('tipewt').disabled=true;
     document.getElementById('nama').value=nama;
    document.getElementById('method').value='update';
}

function del(tipewt)
{
	param='method=delete'+'&tipewt='+tipewt;
	tujuan='pabrik_slave_5mr_bfwt.php';
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




